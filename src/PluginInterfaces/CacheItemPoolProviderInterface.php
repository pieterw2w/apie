<?php


namespace W2w\Lib\Apie\PluginInterfaces;

use Psr\Cache\CacheItemPoolInterface;

interface CacheItemPoolProviderInterface
{
    public function getCacheItemPool(): CacheItemPoolInterface;
}
