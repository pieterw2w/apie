<?php

namespace W2w\Lib\Apie\Plugins\ValueObject;

use W2w\Lib\Apie\Interfaces\ValueObjectInterface;
use W2w\Lib\Apie\PluginInterfaces\NormalizerProviderInterface;
use W2w\Lib\Apie\PluginInterfaces\SchemaProviderInterface;
use W2w\Lib\Apie\Plugins\ValueObject\Normalizers\ValueObjectNormalizer;
use W2w\Lib\Apie\Plugins\ValueObject\Schema\ValueObjectSchemaBuilder;

class ValueObjectPlugin implements NormalizerProviderInterface, SchemaProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function getNormalizers(): array
    {
        return [
            new ValueObjectNormalizer()
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getDefinedStaticData(): array
    {
        return [
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getDynamicSchemaLogic(): array
    {
        return [
            ValueObjectInterface::class => new ValueObjectSchemaBuilder()
        ];
    }
}
