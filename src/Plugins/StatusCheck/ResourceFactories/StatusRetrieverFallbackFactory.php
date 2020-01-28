<?php

namespace W2w\Lib\Apie\Plugins\StatusCheck\ResourceFactories;

use W2w\Lib\Apie\Exceptions\BadConfigurationException;
use W2w\Lib\Apie\Interfaces\ApiResourceFactoryInterface;
use W2w\Lib\Apie\Interfaces\ApiResourcePersisterInterface;
use W2w\Lib\Apie\Interfaces\ApiResourceRetrieverInterface;
use W2w\Lib\Apie\Plugins\StatusCheck\DataLayers\StatusCheckRetriever;

class StatusRetrieverFallbackFactory implements ApiResourceFactoryInterface
{
    private $statusChecks;

    public function __construct(iterable $statusChecks)
    {
        $this->statusChecks = $statusChecks;
    }
    /**
     * Returns true if this factory can create this identifier.
     *
     * @param string $identifier
     * @return bool
     */
    public function hasApiResourceRetrieverInstance(string $identifier): bool
    {
        return $identifier === StatusCheckRetriever::class;
    }

    /**
     * Gets an instance of ApiResourceRetrieverInstance
     * @param string $identifier
     * @return ApiResourceRetrieverInterface
     */
    public function getApiResourceRetrieverInstance(string $identifier): ApiResourceRetrieverInterface
    {
        return new StatusCheckRetriever($this->statusChecks);
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
