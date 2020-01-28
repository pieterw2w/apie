<?php

namespace W2w\Lib\Apie\Plugins\Uuid;

use erasys\OpenApi\Spec\v3\Schema;
use Ramsey\Uuid\UuidInterface;
use W2w\Lib\Apie\PluginInterfaces\NormalizerProviderInterface;
use W2w\Lib\Apie\PluginInterfaces\SchemaProviderInterface;
use W2w\Lib\Apie\Plugins\Uuid\Normalizers\UuidNormalizer;

class UuidPlugin implements NormalizerProviderInterface, SchemaProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function getNormalizers(): array
    {
        return [
            new UuidNormalizer()
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getDefinedStaticData(): array
    {
        return [
            UuidInterface::class => new Schema(['type' => 'string', 'format' =>  'uuid'])
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getDynamicSchemaLogic(): array
    {
        return [];
    }
}
