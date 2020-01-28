<?php


namespace W2w\Lib\Apie\Plugins\DateTime;

use DateTimeInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use erasys\OpenApi\Spec\v3\Schema;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use W2w\Lib\Apie\PluginInterfaces\NormalizerProviderInterface;
use W2w\Lib\Apie\PluginInterfaces\SchemaProviderInterface;

final class DateTimePlugin implements NormalizerProviderInterface, SchemaProviderInterface
{
    /**
     * @return NormalizerInterface[]|DenormalizerInterface[]
     */
    public function getNormalizers(): array
    {
        return [
            new DateTimeNormalizer([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'])
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
