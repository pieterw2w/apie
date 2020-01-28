<?php


namespace W2w\Lib\Apie\PluginInterfaces;

interface ResourceProviderInterface
{
    /**
     * Returns a list of Api resources.
     *
     * @return string[]
     */
    public function getResources(): array;
}
