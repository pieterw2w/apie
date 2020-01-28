<?php
namespace W2w\Lib\Apie\Plugins\Carbon;

use DateTimeInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use erasys\OpenApi\Spec\v3\Schema;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use W2w\Lib\Apie\PluginInterfaces\NormalizerProviderInterface;
use W2w\Lib\Apie\PluginInterfaces\SchemaProviderInterface;
use W2w\Lib\Apie\Plugins\Carbon\Normalizers\CarbonNormalizer;

/**
 * Plugin that adds support for Carbon. See https://carbon.nesbot.com/
 */
final class CarbonPlugin implements NormalizerProviderInterface, SchemaProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function getNormalizers(): array
    {
        return [
                new CarbonNormalizer(
                [
                    DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'
                ]
            )
        ];
    }

    /**
     * @return Schema[]
     */
    public function getDefinedStaticData(): array
    {
        AnnotationReader::addGlobalIgnoredName('alias');
        return [
            DateTimeInterface::class => new Schema(['type' => 'string', 'format' => 'date-time'])
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
