<?php


namespace W2w\Lib\Apie\Plugins\PrimaryKey\Schema;


use erasys\OpenApi\Spec\v3\Schema;
use W2w\Lib\Apie\Core\ClassResourceConverter;
use W2w\Lib\Apie\OpenApiSchema\Factories\SchemaFactory;
use W2w\Lib\Apie\OpenApiSchema\OpenApiSchemaGenerator;
use W2w\Lib\Apie\PluginInterfaces\DynamicSchemaInterface;
use W2w\Lib\Apie\PluginInterfaces\FrameworkConnectionInterface;

class ApiResourceLinkSchemaBuilder implements DynamicSchemaInterface
{
    /**
     * @var FrameworkConnectionInterface
     */
    private $connection;

    public function __construct(FrameworkConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(
        string $resourceClass,
        string $operation,
        array $groups,
        int $recursion,
        OpenApiSchemaGenerator $generator
    ): ?Schema {
        if ($recursion > 0 && $operation === 'get') {
            return SchemaFactory::createStringSchema(
                'path',
                $this->connection->getExampleUrl($resourceClass),
                true
            );
        }
        return $generator->createSchema($resourceClass, $operation, $groups);
    }
}
