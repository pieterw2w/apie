<?php


namespace W2w\Lib\Apie\Plugins\ValueObject\Schema;

use W2w\Lib\Apie\OpenApiSchema\OpenApiSchemaGenerator;
use W2w\Lib\Apie\PluginInterfaces\DynamicSchemaInterface;

class ValueObjectSchemaBuilder implements DynamicSchemaInterface
{
    public function __invoke(
        string $resourceClass,
        string $operation,
        array $groups,
        int $recursion,
        OpenApiSchemaGenerator $generator
    ) {
        return $resourceClass::toSchema();
    }
}
