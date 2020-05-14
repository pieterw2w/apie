<?php


namespace W2w\Lib\Apie\Plugins\PrimaryKey;


use erasys\OpenApi\Spec\v3\Schema;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use W2w\Lib\Apie\Core\Resources\ApiResources;
use W2w\Lib\Apie\Exceptions\BadConfigurationException;
use W2w\Lib\Apie\PluginInterfaces\ApieAwareInterface;
use W2w\Lib\Apie\PluginInterfaces\ApieAwareTrait;
use W2w\Lib\Apie\PluginInterfaces\NormalizerProviderInterface;
use W2w\Lib\Apie\PluginInterfaces\SchemaProviderInterface;
use W2w\Lib\Apie\Plugins\PrimaryKey\Normalizers\ApiePrimaryKeyNormalizer;
use W2w\Lib\Apie\Plugins\PrimaryKey\Normalizers\PrimaryKeyReferenceNormalizer;
use W2w\Lib\Apie\Plugins\PrimaryKey\ValueObjects\PrimaryKeyReference;

/**
 * Core Apie plugin to map api resources to string urls for child objects.
 */
class PrimaryKeyPlugin implements NormalizerProviderInterface, ApieAwareInterface, SchemaProviderInterface
{
    use ApieAwareTrait;

    /**
     * @return NormalizerInterface[]|DenormalizerInterface[]
     */
    public function getNormalizers(): array
    {
        $primaryKeyNormalizer = new ApiePrimaryKeyNormalizer(
            new ApiResources($this->getApie()->getResources()),
            $this->getApie()->getIdentifierExtractor(),
            $this->getApie()->getApiResourceMetadataFactory(),
            $this->getApie()->getClassResourceConverter(),
            $this->getBaseUrl()
        );
        return [
            new PrimaryKeyReferenceNormalizer(),
            $primaryKeyNormalizer,
        ];
    }

    /**
     * Returns base url if one is set up.
     *
     * @return string
     */
    private function getBaseUrl(): string
    {
        try {
            return $this->getApie()->getBaseUrl();
        } catch (BadConfigurationException $exception) {
            return '';
        }
    }

    /**
     * @return Schema[]
     */
    public function getDefinedStaticData(): array
    {
        return [
            PrimaryKeyReference::class => new Schema([
                'type' => 'string',
                'format' => 'path',
            ]),
        ];
    }

    /**
     * @return callable[]
     */
    public function getDynamicSchemaLogic(): array
    {
        return [];
    }
}
