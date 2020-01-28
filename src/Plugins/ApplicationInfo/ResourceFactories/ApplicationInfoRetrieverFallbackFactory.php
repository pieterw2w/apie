<?php

namespace W2w\Lib\Apie\Plugins\ApplicationInfo\ResourceFactories;

use W2w\Lib\Apie\Exceptions\BadConfigurationException;
use W2w\Lib\Apie\Interfaces\ApiResourceFactoryInterface;
use W2w\Lib\Apie\Interfaces\ApiResourcePersisterInterface;
use W2w\Lib\Apie\Interfaces\ApiResourceRetrieverInterface;
use W2w\Lib\Apie\Plugins\ApplicationInfo\DataLayers\ApplicationInfoRetriever;
use W2w\Lib\Apie\Plugins\ApplicationInfo\Guesser\AppGuesser;

class ApplicationInfoRetrieverFallbackFactory implements ApiResourceFactoryInterface
{
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

    /**
     * @var bool|null
     */
    private $debug;

    public function __construct(
        ?string $appName = null,
        ?string $environment = null,
        ?string $hash = null,
        bool $debug = true
    ) {
        $this->appName = $appName;
        $this->environment = $environment;
        $this->hash = $hash;
        $this->debug = $debug;
    }

    /**
     * Returns true if this factory can create this identifier.
     *
     * @param string $identifier
     * @return bool
     */
    public function hasApiResourceRetrieverInstance(string $identifier): bool
    {
        return $identifier === ApplicationInfoRetriever::class;
    }

    /**
     * Gets an instance of ApiResourceRetrieverInstance
     * @param string $identifier
     * @return ApiResourceRetrieverInterface
     */
    public function getApiResourceRetrieverInstance(string $identifier): ApiResourceRetrieverInterface
    {
        $appName = $this->appName ?? AppGuesser::determineApp();
        $environment = $this->environment ?? AppGuesser::determineEnvironment($this->debug);
        $hash = $this->hash ?? AppGuesser::determineHash();
        return new ApplicationInfoRetriever($appName, $environment, $hash, $this->debug);
    }

    /**
     * Returns true if this factory can create this identifier.
     *
     * @param string $identifier
     * @return bool
     */
    public function hasApiResourcePersisterInstance(string $identifier): bool
    {
        return false;
    }

    /**
     * Gets an instance of ApiResourceRetrieverInstance
     * @param string $identifier
     * @return ApiResourcePersisterInterface
     */
    public function getApiResourcePersisterInstance(string $identifier): ApiResourcePersisterInterface
    {
        throw new BadConfigurationException('This call is not supposed to be called');
    }
}
