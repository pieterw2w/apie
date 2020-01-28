<?php
namespace W2w\Lib\Apie\Plugins\Core\DataLayers;

use W2w\Lib\Apie\Core\SearchFilters\SearchFilterRequest;
use W2w\Lib\Apie\Exceptions\ResourceNotFoundException;
use W2w\Lib\Apie\Interfaces\ApiResourcePersisterInterface;
use W2w\Lib\Apie\Interfaces\ApiResourceRetrieverInterface;

/**
 * Persists and retrieves nothing. Only created for entities that require POST, but do not need any storage.
 */
class NullDataLayer implements ApiResourcePersisterInterface, ApiResourceRetrieverInterface
{
    /**
     * {@inheritDoc}
     */
    public function persistNew($resource, array $context = [])
    {
        return $resource;
    }

    /**
     * {@inheritDoc}
     */
    public function persistExisting($resource, $int, array $context = [])
    {
        return $resource;
    }

    /**
     * {@inheritDoc}
     */
    public function remove(string $resourceClass, $id, array $context)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function retrieve(string $resourceClass, $id, array $context)
    {
        throw new ResourceNotFoundException($id);
    }

    /**
     * {@inheritDoc}
     */
    public function retrieveAll(string $resourceClass, array $context, SearchFilterRequest $searchFilterRequest
    ): iterable {
        return [];
    }
}
