<?php


namespace W2w\Lib\Apie\Plugins\PrimaryKey\Schema;


use erasys\OpenApi\Spec\v3\Schema;
use W2w\Lib\Apie\Core\ClassResourceConverter;
use W2w\Lib\Apie\OpenApiSchema\OpenApiSchemaGenerator;
use W2w\Lib\Apie\PluginInterfaces\DynamicSchemaInterface;

class ApiResourceLinkSchemaBuilder implements DynamicSchemaInterface
{
    /**
     * @var ClassResourceConverter
     */
    private $classResourceConverter;

    public function __construct(ClassResourceConverter $classResourceConverter)
    {
        $this->classResourceConverter = $classResourceConverter;
    }
    /**
     * @param string $resourceClass
     * @param string $operation
     * @param array $groups
     * @param int $recursion
     * @param OpenApiSchemaGenerator $generator
     */
    public function __invoke(
        string $resourceClass,
        string $operation,
        array $groups,
        int $recursion,
        OpenApiSchemaGenerator $generator
    ) {
        if ($recursion > 0 && $operation === 'get') {
            return new Schema([
                'type' => 'string',
                'format' => 'path',
                'nullable' => true,
                'example' => '/' . $this->classResourceConverter->normalize($resourceClass) . '/12345',
            ]);
        }
        return $generator->createSchema($resourceClass, $operation, $groups);
    }
}
