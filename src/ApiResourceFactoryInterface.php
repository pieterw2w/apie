<?php

namespace W2w\Lib\Apie;

use W2w\Lib\Apie\Persisters\ApiResourcePersisterInterface;
use W2w\Lib\Apie\Retrievers\ApiResourceRetrieverInterface;

/**
 * Interface for a factory that creates ApiResourceRetrieverInterface and ApiResourcePersisterInterface instances.
 */
interface ApiResourceFactoryInterface
{
    /**
     * Gets an instance of ApiResourceRetrieverInstance
     * @param string $identifier
     * @return ApiResourceRetrieverInterface
     */
    public function getApiResourceRetrieverInstance(string $identifier): ApiResourceRetrieverInterface;

    /**
     * Gets an instance of ApiResourceRetrieverInstance
     * @param string $identifier
     * @return ApiResourcePersisterInterface
     */
    public function getApiResourcePersisterInstance(string $identifier): ApiResourcePersisterInterface;
}
