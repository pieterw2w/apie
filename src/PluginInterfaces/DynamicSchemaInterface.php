<?php

namespace W2w\Lib\Apie\PluginInterfaces;

use erasys\OpenApi\Spec\v3\Schema;
use W2w\Lib\Apie\OpenApiSchema\OpenApiSchemaGenerator;

/**
 * Can be used instead of a closure for SchemaProviderInterface::getDynamicSchemaLogic() to get better typehinting.
 */
interface DynamicSchemaInterface
{
    /**
     * Invokable method to generate a schema for a specific resource class in a specific context.
     *
     * @param string $resourceClass
     * @param string $operation
     * @param array $groups
     * @param int $recursion
     * @param OpenApiSchemaGenerator $generator
     *
     * @return Schema|null
     */
    public function __invoke(
        string $resourceClass,
        string $operation,
        array $groups,
        int $recursion,
        OpenApiSchemaGenerator $generator
    ): ?Schema;
}
