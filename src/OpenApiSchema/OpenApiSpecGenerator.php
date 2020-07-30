<?php

namespace W2w\Lib\Apie\OpenApiSchema;

use erasys\OpenApi\Spec\v3 as OASv3;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use W2w\Lib\Apie\Core\ApiResourceMetadataFactory;
use W2w\Lib\Apie\Core\ClassResourceConverter;
use W2w\Lib\Apie\Core\IdentifierExtractor;
use W2w\Lib\Apie\Core\Resources\ApiResourcesInterface;
use W2w\Lib\Apie\Interfaces\SearchFilterProviderInterface;
use W2w\Lib\Apie\OpenApiSchema\SubActions\SubAction;
use W2w\Lib\Apie\OpenApiSchema\SubActions\SubActionContainer;

/**
 * Class that generated an OpenAPI spec from a list of API resources.
 */
class OpenApiSpecGenerator
{
    private $apiResources;

    private $converter;

    private $info;

    private $schemaGenerator;

    private $apiResourceMetadataFactory;

    private $identifierExtractor;

    private $baseUrl;

    private $subActionContainer;

    private $addSpecsHook;

    private $nameConverter;

    public function __construct(
        ApiResourcesInterface $apiResources,
        ClassResourceConverter $converter,
        OASv3\Info $info,
        OpenApiSchemaGenerator $schemaGenerator,
        ApiResourceMetadataFactory $apiResourceMetadataFactory,
        IdentifierExtractor $identifierExtractor,
        string $baseUrl,
        SubActionContainer $subActionContainer,
        NameConverterInterface $nameConverter,
        ?callable $addSpecsHook = null
    ) {
        $this->apiResources = $apiResources;
        $this->converter = $converter;
        $this->info = $info;
        $this->schemaGenerator = $schemaGenerator;
        $this->apiResourceMetadataFactory = $apiResourceMetadataFactory;
        $this->identifierExtractor = $identifierExtractor;
        $this->baseUrl = $baseUrl;
        $this->subActionContainer = $subActionContainer;
        $this->nameConverter = $nameConverter;
        $this->addSpecsHook = $addSpecsHook;
    }

    private function getIdentifierKey(string $className): ?string
    {
        $context = $this->apiResourceMetadataFactory->getMetadata($className)->getContext();
        return $this->identifierExtractor->getIdentifierKeyOfClass($className, $context);
    }

    /**
     * Gets an OpenAPI spec document.
     *
     * @return OASv3\Document
     */
    public function getOpenApiSpec(): OASv3\Document
    {
        $paths = [];
        foreach ($this->apiResources->getApiResources() as $apiResourceClass) {
            $path = $this->converter->normalize($apiResourceClass);
            $identifierName = $this->getIdentifierKey($apiResourceClass);
            $paths['/' . $path] = $this->convertAllToPathItem($apiResourceClass, $path);
            if ($identifierName) {
                $paths['/' . $path . '/{' . $identifierName . '}'] = $this->convertToPathItem($apiResourceClass, $path, $identifierName);
                foreach ($this->subActionContainer->getSubActionsForResourceClass($apiResourceClass) as $key => $subAction) {
                    $paths['/' . $path . '/{' . $identifierName . '}/' . $key] = $this->convertSubActionToPathItem($subAction, $path, $identifierName);
                }
            }
        }

        $stringSchema = new OASv3\Schema(['type' => 'string']);
        $stringOrIntSchema = new OASv3\Schema(['oneOf' => [$stringSchema, new OASv3\Schema(['type' => 'integer'])]]);
        $stringArraySchema = new OASv3\Schema(['type' => 'array', 'items' => $stringSchema]);

        $errorSchema = new OASv3\Reference('#/components/schemas/Error');

        $validationErrorSchema = new OASv3\Schema([
            'type'       => 'object',
            'properties' => [
                'type'    => $stringSchema,
                'message' => $stringSchema,
                'code'    => $stringOrIntSchema,
                'trace'   => $stringSchema,
                'errors'  => new OASv3\Schema([
                    'type'       => 'object',
                    'additionalProperties' => $stringArraySchema
                ]),
            ],
            'xml' => new OASv3\Xml(['name' => 'response']),
        ]);

        $doc = new OASv3\Document(
            $this->info,
            $paths,
            '3.0.1',
            [
                'servers' => [
                    new OASv3\Server($this->baseUrl),
                ],
                'components' => new OASv3\Components([
                    'schemas' => [
                        'Error' => new OASv3\Schema([
                            'type'       => 'object',
                            'properties' => [
                                'type'    => $stringSchema,
                                'message' => $stringSchema,
                                'code'    => $stringOrIntSchema,
                                'trace'   => $stringSchema,
                            ],
                            'xml' => new OASv3\Xml(['name' => 'response']),
                        ]),
                    ],
                    'headers' => [
                        'x-ratelimit-limit' => new Oasv3\Header(
                            'Request limit per hour',
                            [
                                'example' => 100,
                                'schema'  => new OASv3\Schema([
                                    'type' => 'integer',
                                ]),
                            ]
                        ),
                        'x-ratelimit-remaining' => new Oasv3\Header(
                            'Request limit per hour',
                            [
                                'example' => 94,
                                'schema'  => new OASv3\Schema([
                                    'type' => 'integer',
                                ]),
                            ]
                        ),
                    ],
                    'responses' => [
                        'InvalidFormat' => new OASv3\Response(
                            'The body input could not be parsed',
                            [
                                'application/json' => new OASv3\MediaType(
                                    [
                                        'schema' => $errorSchema,
                                    ]
                                ),
                                'application/xml' => new OASv3\MediaType(
                                    [
                                        'schema' => $errorSchema,
                                    ]
                                ),
                            ]
                        ),
                        'ValidationError' => new OASv3\Response(
                            'The body input was in a proper format, but the input values were not valid',
                            [
                                'application/json' => new OASv3\MediaType(
                                    [
                                        'schema' => $validationErrorSchema,
                                    ]
                                ),
                                'application/xml' => new OASv3\MediaType(
                                    [
                                        'schema' => $validationErrorSchema,
                                    ]
                                ),
                            ]
                        ),
                        'TooManyRequests' => new OASv3\Response(
                            'Too many requests per seconds were sent',
                            [
                                'application/json' => new OASv3\MediaType(
                                    [
                                        'schema' => $errorSchema,
                                    ]
                                ),
                                'application/xml' => new OASv3\MediaType(
                                    [
                                        'schema' => $errorSchema,
                                    ]
                                ),
                            ]
                        ),
                        'MaintenanceMode' => new OASv3\Response(
                            'App is in maintenance mode',
                            [
                                'application/json' => new OASv3\MediaType(
                                    [
                                        'schema' => $errorSchema,
                                    ]
                                ),
                                'application/xml' => new OASv3\MediaType(
                                    [
                                        'schema' => $errorSchema,
                                    ]
                                ),
                            ]
                        ),
                        'NotFound' => new OASv3\Response(
                            'Response when resource could not be found',
                            [
                                'application/json' => new OASv3\MediaType(
                                    [
                                        'schema' => $errorSchema,
                                    ]
                                ),
                                'application/xml' => new OASv3\MediaType(
                                    [
                                        'schema' => $errorSchema,
                                    ]
                                ),
                            ]
                        ),
                        'NotAuthorized' => new OASv3\Response(
                            'You have no permission to do this call',
                            [
                                'application/json' => new OASv3\MediaType(
                                    [
                                        'schema' => $errorSchema,
                                    ]
                                ),
                                'application/xml' => new OASv3\MediaType(
                                    [
                                        'schema' => $errorSchema,
                                    ]
                                ),
                            ]
                        ),
                        'InternalError' => new OASv3\Response(
                            'An internal error occured',
                            [
                                'application/json' => new OASv3\MediaType(
                                    [
                                        'schema' => $errorSchema,
                                    ]
                                ),
                                'application/xml' => new OASv3\MediaType(
                                    [
                                        'schema' => $errorSchema,
                                    ]
                                ),
                            ]
                        ),
                        'ServerDependencyError' => new OASv3\Response(
                            'The server required an external response which threw an error',
                            [
                                'application/json' => new OASv3\MediaType(
                                    [
                                        'schema' => $errorSchema,
                                    ]
                                ),
                                'application/xml' => new OASv3\MediaType(
                                    [
                                        'schema' => $errorSchema,
                                    ]
                                ),
                            ]
                        ),
                    ],
                ]),
            ]
        );
        if (is_callable($this->addSpecsHook)) {
            $res = call_user_func($this->addSpecsHook, $doc);
            if ($res instanceof OASv3\Document) {
                return $res;
            }
        }

        return $doc;
    }

    /**
     * Returns the default HTTP headers we generated for every REST api call.
     *
     * @return array
     */
    private function getDefaultHeaders(): array
    {
        return [
            'x-ratelimit-limit'     => new OASv3\Reference('#/components/headers/x-ratelimit-limit'),
            'x-ratelimit-remaining' => new OASv3\Reference('#/components/headers/x-ratelimit-remaining'),
        ];
    }

    private function convertSubActionToRequestBody(SubAction $subAction): ?OASv3\RequestBody
    {
        $properties = [];
        foreach ($subAction->getArguments() as $fieldName => $type) {
            $denormalizedFieldName = $this->nameConverter->denormalize($fieldName);
            if ($type === null || !$type->getClassName()) {
                $properties[$denormalizedFieldName] = $this->schemaGenerator->convertTypeToSchema($type, 'post', ['write', 'post'], 0);
                continue;
            }
            $properties[$denormalizedFieldName] = $this->schemaGenerator->createSchema($type->getClassName(), 'post', ['write', 'post']);
        }
        $jsonSchema = new OASv3\Schema([
            'type' => 'object',
            'properties' => $properties,
        ]);
        $xmlSchema = unserialize(serialize($jsonSchema));
        $xmlSchema->xml = new OASv3\Xml(['name' => 'item']);

        return new OASv3\RequestBody(
            [
                'application/json' => new OASv3\MediaType(
                    [
                        'schema' => $jsonSchema,
                    ]
                ),
                'application/xml' => new OASv3\MediaType(
                    [
                        'schema' => $xmlSchema,
                    ]
                ),
            ],
            'the resource as JSON to persist',
            true
        );
    }

    /**
     * Returns the content OpenAPI spec for a resource class and a certain operation.
     *
     * @param string $apiResourceClass
     * @param string $operation
     * @return OASv3\MediaType[]
     */
    private function convertToContent(string $apiResourceClass, string $operation): array
    {
        $readWrite = $this->determineReadWrite($operation);
        $jsonSchema = $this->schemaGenerator->createSchema($apiResourceClass, $operation, [$operation, $readWrite]);
        $xmlSchema = unserialize(serialize($jsonSchema));
        $xmlSchema->xml = new OASv3\Xml(['name' => 'item']);

        return [
            'application/json' => new OASv3\MediaType(
                [
                    'schema' => $jsonSchema,
                ]
            ),
            'application/xml' => new OASv3\MediaType(
                [
                    'schema' => $xmlSchema,
                ]
            ),
        ];
    }

    /**
     * Returns the content OpenAPI spec for a resource class when it returns an array of resources.
     *
     * @param string $apiResourceClass
     * @param string $operation
     * @return OASv3\MediaType[]
     */
    private function convertToContentArray(string $apiResourceClass, string $operation): array
    {
        $readWrite = $this->determineReadWrite($operation);
        $jsonSchema = $this->schemaGenerator->createSchema($apiResourceClass, $operation, [$operation, $readWrite]);
        $xmlSchema = $this->schemaGenerator->createSchema($apiResourceClass, $operation, [$operation, $readWrite]);
        $xmlSchema->xml = new OASv3\Xml(['name' => 'item']);

        return [
            'application/json' => new OASv3\MediaType(
                [
                    'schema' => new OASv3\Schema([
                        'type'  => 'array',
                        'items' => $jsonSchema,
                    ]),
                ]
            ),
            'application/xml' => new OASv3\MediaType(
                [
                    'schema' => new OASv3\Schema([
                        'type'  => 'array',
                        'items' => $xmlSchema,
                        'xml'   => new OASv3\Xml(['name' => 'response']),
                    ]),
                ]
            ),
        ];
    }

    /**
     * Determine if the operation is a read or a write.
     *
     * @param string $operation
     * @return string
     */
    private function determineReadWrite(string $operation): string
    {
        if ($operation === 'post' || $operation === 'put') {
            return 'write';
        }

        return 'read';
    }

    /**
     * Sluggify resource name for the operation id.
     *
     * @param string $resourceName
     * @return string|string[]|null
     */
    private function sluggify(string $resourceName)
    {
        return (new CamelCaseToSnakeCaseNameConverter(null, false))->denormalize($resourceName);
    }

    /**
     * Returns all paths of an api resource without an id in the url.
     *
     * @param string $apiResourceClass
     * @param string $resourceName
     * @return OASv3\PathItem
     */
    private function convertAllToPathItem(string $apiResourceClass, string $resourceName): OASv3\PathItem
    {
        $paths = [];

        if ($this->allowed($apiResourceClass, 'all')) {
            $paths['get'] = new OASv3\Operation(
                [
                    '200' => new OASv3\Response(
                        'Retrieves all instances of ' . $resourceName,
                        $this->convertToContentArray($apiResourceClass, 'get'),
                        $this->getDefaultHeaders()
                    ),
                    '401' => new OASv3\Reference('#/components/responses/NotAuthorized'),
                    '429' => new OASv3\Reference('#/components/responses/TooManyRequests'),
                    '500' => new OASv3\Reference('#/components/responses/InternalError'),
                    '502' => new OASv3\Reference('#/components/responses/ServerDependencyError'),
                    '503' => new OASv3\Reference('#/components/responses/MaintenanceMode'),
                ],
                'resourceGetAll' . $this->sluggify($resourceName),
                'get/search all instances of ' . $resourceName,
                [
                    'tags'       => [$resourceName],
                    'parameters' => [
                        new OASv3\Parameter('page', 'query', 'pagination index counting from 0', ['schema' => new OASv3\Schema(['type' => 'integer', 'minimum' => 0])]),
                        new OASv3\Parameter('limit', 'query', 'number of results', ['schema' => new OASv3\Schema(['type' => 'integer', 'minimum' => 1])]),
                    ],
                ]
            );
            $metadata = $this->apiResourceMetadataFactory->getMetadata($apiResourceClass);
            $retriever = $metadata->hasResourceRetriever() ? $metadata->getResourceRetriever() : null;
            if ($retriever instanceof SearchFilterProviderInterface) {
                foreach ($retriever->getSearchFilter($metadata)->getAllPrimitiveSearchFilter() as $name => $filter) {
                    $schema = $filter->getSchemaForFilter();
                    $paths['get']->parameters[] = new OASv3\Parameter(
                        $name,
                        'query',
                        'search filter ' . $name,
                        ['schema' => $schema]
                    );
                }
            }
        }

        if ($this->allowed($apiResourceClass, 'post')) {
            $paths['post'] = new OASv3\Operation(
                [
                    '200' => new OASv3\Response(
                        'Creates a new instance of ' . $resourceName,
                        $this->convertToContent($apiResourceClass, 'get'),
                        $this->getDefaultHeaders()
                    ),
                    '401' => new OASv3\Reference('#/components/responses/NotAuthorized'),
                    '415' => new OASv3\Reference('#/components/responses/InvalidFormat'),
                    '422' => new OASv3\Reference('#/components/responses/ValidationError'),
                    '429' => new OASv3\Reference('#/components/responses/TooManyRequests'),
                    '500' => new OASv3\Reference('#/components/responses/InternalError'),
                    '502' => new OASv3\Reference('#/components/responses/ServerDependencyError'),
                    '503' => new OASv3\Reference('#/components/responses/MaintenanceMode'),
                ],
                'resourcePostSingle' . $this->sluggify($resourceName),
                'create a new single instance of ' . $resourceName,
                [
                    'tags'        => [$resourceName],
                    'requestBody' => new OASv3\RequestBody(
                        $this->convertToContent($apiResourceClass, 'post'),
                        'the resource as JSON to persist',
                        true
                    ),
                ]
            );
        }

        return new OASv3\PathItem($paths);
    }

    /**
     * Creates PathItem for sub actions.
     *
     * @param SubAction $subAction
     * @param string $resourceName
     * @param string $identifierName
     * @return OASv3\PathItem
     */
    private function convertSubActionToPathItem(SubAction $subAction, string $resourceName, string $identifierName): OASv3\PathItem
    {
        $paths = [
            'parameters' => [
                new OASv3\Parameter($identifierName, 'path', 'the id of the resource', ['required' => true, 'schema' => new OASv3\Schema(['type' => 'string'])]),
            ],
        ];
        $paths['post'] = new OASv3\Operation(
            [
                '200' => new OASv3\Response(
                    'Retrieves return value of ' . $subAction->getName(),
                    $subAction->getReturnTypehint()->getClassName() ? $this->convertToContent($subAction->getReturnTypehint()->getClassName(), 'get') : null,
                    $this->getDefaultHeaders()
                ),
                '401' => new OASv3\Reference('#/components/responses/NotAuthorized'),
                '404' => new OASv3\Reference('#/components/responses/NotFound'),
                '429' => new OASv3\Reference('#/components/responses/TooManyRequests'),
                '500' => new OASv3\Reference('#/components/responses/InternalError'),
                '502' => new OASv3\Reference('#/components/responses/ServerDependencyError'),
                '503' => new OASv3\Reference('#/components/responses/MaintenanceMode'),
            ],
            'resourcePostSubAction' . $this->sluggify($resourceName . '_' . $subAction->getName()),
            $subAction->getSummary(),
            [
                'tags' => [$resourceName],
                'requestBody' => $this->convertSubActionToRequestBody($subAction),
            ]
        );
        return new OASv3\PathItem($paths);
    }

    /**
     * Returns all paths of an api resource with an id in the url.
     * @param string $apiResourceClass
     * @param string $resourceName
     * @return OASv3\PathItem
     */
    private function convertToPathItem(string $apiResourceClass, string $resourceName, string $identifierName): OASv3\PathItem
    {
        $paths = [
            'parameters' => [
                new OASv3\Parameter($identifierName, 'path', 'the id of the resource', ['required' => true, 'schema' => new OASv3\Schema(['type' => 'string'])]),
            ],
        ];
        if ($this->allowed($apiResourceClass, 'get')) {
            $paths['get'] = new OASv3\Operation(
                [
                    '200' => new OASv3\Response(
                        'Retrieves a single instance of ' . $resourceName,
                        $this->convertToContent($apiResourceClass, 'get'),
                        $this->getDefaultHeaders()
                    ),
                    '401' => new OASv3\Reference('#/components/responses/NotAuthorized'),
                    '404' => new OASv3\Reference('#/components/responses/NotFound'),
                    '429' => new OASv3\Reference('#/components/responses/TooManyRequests'),
                    '500' => new OASv3\Reference('#/components/responses/InternalError'),
                    '502' => new OASv3\Reference('#/components/responses/ServerDependencyError'),
                    '503' => new OASv3\Reference('#/components/responses/MaintenanceMode'),
                ],
                'resourceGetSingle' . $this->sluggify($resourceName),
                'retrieve a single instance of ' . $resourceName,
                [
                    'tags' => [$resourceName],
                ]
            );
        }
        if ($this->allowed($apiResourceClass, 'delete')) {
            $paths['delete'] = new OASv3\Operation(
                [
                    '204' => new OASv3\Response(
                        'Deletes a single instance of ' . $resourceName,
                        null,
                        $this->getDefaultHeaders()
                    ),
                    '401' => new OASv3\Reference('#/components/responses/NotAuthorized'),
                    '404' => new OASv3\Reference('#/components/responses/NotFound'),
                    '429' => new OASv3\Reference('#/components/responses/TooManyRequests'),
                    '500' => new OASv3\Reference('#/components/responses/InternalError'),
                    '502' => new OASv3\Reference('#/components/responses/ServerDependencyError'),
                    '503' => new OASv3\Reference('#/components/responses/MaintenanceMode'),
                ],
                'resourceDeleteSingle' . $this->sluggify($resourceName),
                'delete a single instance of ' . $resourceName,
                [
                    'tags' => [$resourceName],
                ]
            );
        }
        if ($this->allowed($apiResourceClass, 'put')) {
            $paths['put'] = new OASv3\Operation(
                [
                    '200' => new OASv3\Response(
                        'Retrieves and update a single instance of ' . $resourceName,
                        $this->convertToContent($apiResourceClass, 'get'),
                        $this->getDefaultHeaders()
                    ),
                    '401' => new OASv3\Reference('#/components/responses/NotAuthorized'),
                    '404' => new OASv3\Reference('#/components/responses/NotFound'),
                    '415' => new OASv3\Reference('#/components/responses/InvalidFormat'),
                    '422' => new OASv3\Reference('#/components/responses/ValidationError'),
                    '429' => new OASv3\Reference('#/components/responses/TooManyRequests'),
                    '500' => new OASv3\Reference('#/components/responses/InternalError'),
                    '502' => new OASv3\Reference('#/components/responses/ServerDependencyError'),
                    '503' => new OASv3\Reference('#/components/responses/MaintenanceMode'),
                ],
                'resourcePutSingle' . $this->sluggify($resourceName),
                'modify a single instance of ' . $resourceName,
                [
                    'tags'        => [$resourceName],
                    'requestBody' => new OASv3\RequestBody(
                        $this->convertToContent($apiResourceClass, 'put'),
                        'the resource as JSON to persist',
                        true
                    ),
                ]
            );
        }

        return new OASv3\PathItem($paths);
    }

    /**
     * Returns if a specific REST API call is an allowed method.
     *
     * @param string $apiResourceClass
     * @param string $operation
     * @return bool
     */
    private function allowed(string $apiResourceClass, string $operation): bool
    {
        $metadata = $this->apiResourceMetadataFactory->getMetadata($apiResourceClass);
        switch ($operation) {
            case 'all':
                return $metadata->allowGetAll();
            case 'get':
                return $metadata->allowGet();
            case 'post':
                return $metadata->allowPost();
            case 'put':
                return $metadata->allowPut();
            case 'delete':
                return $metadata->allowDelete();
        }
        // @codeCoverageIgnoreStart
        return false;
        // @codeCoverageIgnoreEnd
    }
}
