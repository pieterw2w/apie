<?php


namespace W2w\Lib\Apie\Plugins\PrimaryKey\Schema;


use erasys\OpenApi\Spec\v3\Schema;
use W2w\Lib\Apie\OpenApiSchema\SchemaGenerator;
use W2w\Lib\Apie\PluginInterfaces\DynamicSchemaInterface;

class ApiResourceLinkSchemaBuilder implements DynamicSchemaInterface
{

    /**
     * @param string $resourceClass
     * @param string $operation
     * @param array $groups
     * @param int $recursion
     * @param SchemaGenerator $generator
     */
    public function __invoke(
        string $resourceClass,
        string $operation,
        array $groups,
        int $recursion,
        SchemaGenerator $generator
    ) {
        if ($recursion > 0 && $operation === 'get') {
            return new Schema([
                'type' => 'string',
                'format' => 'path',
            ]);
        }
        return $generator->createSchema($resourceClass, $operation, $groups);
    }
}
