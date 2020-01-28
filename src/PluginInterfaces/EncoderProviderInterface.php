<?php

namespace W2w\Lib\Apie\PluginInterfaces;

use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use W2w\Lib\Apie\Interfaces\FormatRetrieverInterface;

interface EncoderProviderInterface
{
    /**
     * @return EncoderInterface[]|DecoderInterface[]
     */
    public function getEncoders(): array;

    /**
     * @return FormatRetrieverInterface
     */
    public function getFormatRetriever(): FormatRetrieverInterface;
}
