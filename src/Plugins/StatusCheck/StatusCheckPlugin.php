<?php


namespace W2w\Lib\Apie\Plugins\StatusCheck;

use W2w\Lib\Apie\Interfaces\ApiResourceFactoryInterface;
use W2w\Lib\Apie\PluginInterfaces\ApiResourceFactoryProviderInterface;
use W2w\Lib\Apie\PluginInterfaces\ResourceProviderInterface;
use W2w\Lib\Apie\Plugins\StatusCheck\ApiResources\Status;
use W2w\Lib\Apie\Plugins\StatusCheck\ResourceFactories\StatusRetrieverFallbackFactory;

class StatusCheckPlugin implements ResourceProviderInterface, ApiResourceFactoryProviderInterface
{
    private $statusChecks;

    public function __construct(iterable $statusChecks = [])
    {
        $this->statusChecks = $statusChecks;
    }

    public function getApiResourceFactory(): ApiResourceFactoryInterface
    {
        return new StatusRetrieverFallbackFactory($this->statusChecks);
    }

    public function getResources(): array
    {
        return [Status::class];
    }
}
