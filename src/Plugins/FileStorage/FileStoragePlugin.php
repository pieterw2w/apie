<?php

namespace W2w\Lib\Apie\Plugins\FileStorage;

use W2w\Lib\Apie\Interfaces\ApiResourceFactoryInterface;
use W2w\Lib\Apie\PluginInterfaces\ApieAwareInterface;
use W2w\Lib\Apie\PluginInterfaces\ApieAwareTrait;
use W2w\Lib\Apie\PluginInterfaces\ApiResourceFactoryProviderInterface;
use W2w\Lib\Apie\Plugins\FileStorage\ResourceFactories\FileStorageDataLayerFactory;

class FileStoragePlugin implements ApiResourceFactoryProviderInterface, ApieAwareInterface
{
    use ApieAwareTrait;

    private $path;

    /**
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function getApiResourceFactory(): ApiResourceFactoryInterface
    {
        return new FileStorageDataLayerFactory($this->path, $this->getApie()->getPropertyAccessor());
    }
}
