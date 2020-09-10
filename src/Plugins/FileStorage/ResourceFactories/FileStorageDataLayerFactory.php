<?php

namespace W2w\Lib\Apie\Plugins\FileStorage\ResourceFactories;

use W2w\Lib\Apie\Core\IdentifierExtractor;
use W2w\Lib\Apie\Interfaces\ApiResourceFactoryInterface;
use W2w\Lib\Apie\Interfaces\ApiResourcePersisterInterface;
use W2w\Lib\Apie\Interfaces\ApiResourceRetrieverInterface;
use W2w\Lib\Apie\Plugins\FileStorage\DataLayers\FileStorageDataLayer;

class FileStorageDataLayerFactory implements ApiResourceFactoryInterface
{
    private $path;

    /**
     * @var IdentifierExtractor
     */
    private $identifierExtractor;

    public function __construct(string $path, IdentifierExtractor $identifierExtractor)
    {
        $this->path = $path;
        $this->identifierExtractor = $identifierExtractor;
    }

    /**
     * Returns true if this factory can create this identifier.
     *
     * @param string $identifier
     * @return bool
     */
    public function hasApiResourceRetrieverInstance(string $identifier): bool
    {
        return $identifier === FileStorageDataLayer::class;
    }

    /**
     * Gets an instance of ApiResourceRetrieverInstance
     * @param string $identifier
     * @return ApiResourceRetrieverInterface
     */
    public function getApiResourceRetrieverInstance(string $identifier): ApiResourceRetrieverInterface
    {
        return new FileStorageDataLayer($this->path, $this->identifierExtractor);
    }

    /**
     * Returns true if this factory can create this identifier.
     *
     * @param string $identifier
     * @return bool
     */
    public function hasApiResourcePersisterInstance(string $identifier): bool
    {
        return $identifier === FileStorageDataLayer::class;
    }

    /**
     * Gets an instance of ApiResourceRetrieverInstance
     * @param string $identifier
     * @return ApiResourcePersisterInterface
     */
    public function getApiResourcePersisterInstance(string $identifier): ApiResourcePersisterInterface
    {
        return new FileStorageDataLayer($this->path, $this->identifierExtractor);
    }
}
