<?php

namespace W2w\Lib\Apie\PluginInterfaces;

use W2w\Lib\Apie\OpenApiSchema\SchemaGenerator;

/**
 * Can be used instead of a closure for SchemaProviderInterface::getDynamicSchemaLogic() to get better typehinting.
 */
interface DynamicSchemaInterface
{
    public function __invoke(
        string $resourceClass,
        string $operation,
        array $groups,
        int $recursion,
        SchemaGenerator $generator
    );
}
