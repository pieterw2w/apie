<?php

namespace W2w\Lib\Apie\PluginInterfaces;

use W2w\Lib\ApieObjectAccessNormalizer\ObjectAccess\ObjectAccessInterface;

interface ObjectAccessProviderInterface
{
    /**
     * @return ObjectAccessInterface[]
     */
    public function getObjectAccesses(): array;
}
