<?php


namespace W2w\Lib\Apie\Plugins\PrimaryKey;

use erasys\OpenApi\Spec\v3\Schema;
use W2w\Lib\Apie\Core\Resources\ApiResources;
use W2w\Lib\Apie\Exceptions\BadConfigurationException;
use W2w\Lib\Apie\OpenApiSchema\Factories\SchemaFactory;
use W2w\Lib\Apie\PluginInterfaces\ApieAwareInterface;
use W2w\Lib\Apie\PluginInterfaces\ApieAwareTrait;
use W2w\Lib\Apie\PluginInterfaces\NormalizerProviderInterface;
use W2w\Lib\Apie\PluginInterfaces\SchemaProviderInterface;
use W2w\Lib\Apie\Plugins\PrimaryKey\Normalizers\ApiePrimaryKeyNormalizer;
use W2w\Lib\Apie\Plugins\PrimaryKey\Normalizers\PrimaryKeyReferenceNormalizer;
use W2w\Lib\Apie\Plugins\PrimaryKey\Schema\ApiResourceLinkSchemaBuilder;
use W2w\Lib\Apie\Plugins\PrimaryKey\ValueObjects\PrimaryKeyReference;

/**
 * Core Apie plugin to map api resources to string urls for child objects.
 */
class PrimaryKeyPlugin implements NormalizerProviderInterface, ApieAwareInterface, SchemaProviderInterface
{
    use ApieAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function getNormalizers(): array
    {
        $primaryKeyNormalizer = new ApiePrimaryKeyNormalizer(
            new ApiResources($this->getApie()->getResources()),
            $this->getApie()->getIdentifierExtractor(),
            $this->getApie()->getApiResourceMetadataFactory(),
            $this->getApie()->getClassResourceConverter(),
            $this->getApie()->getFrameworkConnection()
        );
        return [
            new PrimaryKeyReferenceNormalizer(),
            $primaryKeyNormalizer,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getDefinedStaticData(): array
    {
        return [
            PrimaryKeyReference::class => SchemaFactory::createStringSchema('path'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getDynamicSchemaLogic(): array
    {
        $res = [];
        $identifierExtractor = $this->getApie()->getIdentifierExtractor();
        $builder = new ApiResourceLinkSchemaBuilder($this->getApie()->getFrameworkConnection());
        foreach ($this->getApie()->getResources() as $resource) {
            if (null !== $identifierExtractor->getIdentifierKeyOfClass($resource)) {
                $res[$resource] = $builder;
            }
        }
        return $res;
    }
}
