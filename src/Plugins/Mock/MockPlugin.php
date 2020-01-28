<?php

namespace W2w\Lib\Apie\Plugins\Mock;

use W2w\Lib\Apie\Interfaces\ApiResourceFactoryInterface;
use W2w\Lib\Apie\PluginInterfaces\ApieAwareInterface;
use W2w\Lib\Apie\PluginInterfaces\ApieAwareTrait;
use W2w\Lib\Apie\PluginInterfaces\ApiResourceFactoryProviderInterface;
use W2w\Lib\Apie\Plugins\Mock\DataLayers\MockApiResourceDataLayer;
use W2w\Lib\Apie\Plugins\Mock\ResourceFactories\MockApiResourceFactory;

final class MockPlugin implements ApiResourceFactoryProviderInterface, ApieAwareInterface
{
    use ApieAwareTrait;

    private $ignoreList = [];

    public function __construct(array $ignoreList = [])
    {
        $this->ignoreList = $ignoreList;
    }

    public function getApiResourceFactory(array $next = []): ApiResourceFactoryInterface
    {
        $apie = $this->getApie();

        return new MockApiResourceFactory(
            new MockApiResourceDataLayer(
                $this->getApie()->getCacheItemPool(),
                $this->getApie()->getIdentifierExtractor(),
                $this->getApie()->getPropertyAccessor()
            ),
            $apie,
            $this->ignoreList
        );
    }
}
