<?php


namespace W2w\Lib\Apie\Plugins\ApplicationInfo;

use W2w\Lib\Apie\Interfaces\ApiResourceFactoryInterface;
use W2w\Lib\Apie\PluginInterfaces\ApieAwareInterface;
use W2w\Lib\Apie\PluginInterfaces\ApieAwareTrait;
use W2w\Lib\Apie\PluginInterfaces\ApiResourceFactoryProviderInterface;
use W2w\Lib\Apie\PluginInterfaces\ResourceProviderInterface;
use W2w\Lib\Apie\Plugins\ApplicationInfo\ApiResources\ApplicationInfo;
use W2w\Lib\Apie\Plugins\ApplicationInfo\ResourceFactories\ApplicationInfoRetrieverFallbackFactory;

class ApplicationInfoPlugin implements ResourceProviderInterface, ApiResourceFactoryProviderInterface, ApieAwareInterface
{
    use ApieAwareTrait;

    /**
     * @var string|null
     */
    private $appName;

    /**
     * @var string|null
     */
    private $environment;

    /**
     * @var string|null
     */
    private $hash;

    public function __construct(
        ?string $appName = null,
        ?string $environment = null,
        ?string $hash = null
    ) {
        $this->appName = $appName;
        $this->environment = $environment;
        $this->hash = $hash;
    }

    /**
     * {@inheritDoc}
     */
    public function getResources(): array
    {
        return [ApplicationInfo::class];
    }

    /**
     * {@inheritDoc}
     */
    public function getApiResourceFactory(): ApiResourceFactoryInterface
    {
        return new ApplicationInfoRetrieverFallbackFactory($this->appName, $this->environment, $this->hash, $this->getApie()->isDebug());
    }
}
