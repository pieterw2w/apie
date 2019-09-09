<?php

namespace W2w\Lib\Apie\Schema;

use W2w\Lib\Apie\ApiResourceMetadataFactory;
use W2w\Lib\Apie\ApiResources;
use W2w\Lib\Apie\ClassResourceConverter;
use erasys\OpenApi\Spec\v3 as OASv3;

class OpenApiSpecGenerator
{
    private $apiResources;

    private $converter;

    private $info;

    private $schemaGenerator;

    private $apiResourceMetadataFactory;

    private $baseUrl;

    public function __construct(
        ApiResources $apiResources,
        ClassResourceConverter $converter,
        OASv3\Info $info,
        SchemaGenerator $schemaGenerator,
        ApiResourceMetadataFactory $apiResourceMetadataFactory,
        string $baseUrl
    ) {
        $this->apiResources = $apiResources;
        $this->converter = $converter;
        $this->info = $info;
        $this->schemaGenerator = $schemaGenerator;
        $this->apiResourceMetadataFactory = $apiResourceMetadataFactory;
        $this->baseUrl = $baseUrl;
    }

    public function getOpenApiSpec(): OASv3\Document
    {
        $paths = [];
        foreach ($this->apiResources->getApiResources() as $apiResourceClass) {
            $path = $this->converter->normalize($apiResourceClass);
            $paths['/' . $path] = $this->convertAllToPathItem($apiResourceClass, $path);
            $paths['/' . $path . '/{id}'] = $this->convertToPathItem($apiResourceClass, $path);
        }

        $stringSchema = new OASv3\Schema(['type' => 'string']);
        $stringOrIntSchema = new OASv3\Schema(['oneOf' => [$stringSchema, new OASv3\Schema(['type' => 'integer'])]]);

        $errorSchema = new OASv3\Reference('#/components/schemas/Error');

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

        return $doc;
    }

    private function getDefaultHeaders(): array
    {
        return [
            'x-ratelimit-limit'     => new OASv3\Reference('#/components/headers/x-ratelimit-limit'),
            'x-ratelimit-remaining' => new OASv3\Reference('#/components/headers/x-ratelimit-remaining'),
        ];
    }

    private function convertToContent(string $apiResourceClass, string $operation): array
    {
        $readWrite = $this->determineReadWrite($operation);
        $jsonSchema = $this->schemaGenerator->createSchema($apiResourceClass, $operation, [$operation, $readWrite]);
        $xmlSchema = $this->schemaGenerator->createSchema($apiResourceClass, $operation, [$operation, $readWrite]);
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

    private function determineReadWrite(string $operation): string
    {
        if ($operation === 'post' || $operation === 'put') {
            return 'write';
        }

        return 'read';
    }

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
                null,
                null,
                [
                    'tags'       => [$resourceName],
                    'parameters' => [
                        new OASv3\Parameter('page', 'query', 'pagination index counting from 0', ['schema' => new OASv3\Schema(['type' => 'integer', 'minimum' => 0])]),
                        new OASv3\Parameter('limit', 'query', 'number of results', ['schema' => new OASv3\Schema(['type' => 'integer', 'minimum' => 1])]),
                    ],
                ]
            );
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
                    '424' => new OASv3\Reference('#/components/responses/ValidationError'),
                    '429' => new OASv3\Reference('#/components/responses/TooManyRequests'),
                    '500' => new OASv3\Reference('#/components/responses/InternalError'),
                    '502' => new OASv3\Reference('#/components/responses/ServerDependencyError'),
                    '503' => new OASv3\Reference('#/components/responses/MaintenanceMode'),
                ],
                null,
                null,
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

    private function convertToPathItem(string $apiResourceClass, string $resourceName): OASv3\PathItem
    {
        $paths = [
            'parameters' => [
                new OASv3\Parameter('id', 'path', 'the id of the resource', ['required' => true, 'schema' => new OASv3\Schema(['type' => 'string'])]),
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
                null,
                null,
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
                null,
                null,
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
                    '424' => new OASv3\Reference('#/components/responses/ValidationError'),
                    '429' => new OASv3\Reference('#/components/responses/TooManyRequests'),
                    '500' => new OASv3\Reference('#/components/responses/InternalError'),
                    '502' => new OASv3\Reference('#/components/responses/ServerDependencyError'),
                    '503' => new OASv3\Reference('#/components/responses/MaintenanceMode'),
                ],
                null,
                null,
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

        return false;
    }
}
