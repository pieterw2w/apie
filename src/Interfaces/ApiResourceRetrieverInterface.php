<?php

namespace W2w\Lib\Apie\Interfaces;

use W2w\Lib\Apie\Core\SearchFilters\SearchFilterRequest;

/**
 * Interface for a service to retrieve an api resource by an id.
 */
interface ApiResourceRetrieverInterface
{
    /**
     * Retrieves a single resource by some identifier.
     *
     * @param string $resourceClass
     * @param mixed $id
     * @param array $context
     * @return mixed
     */
    public function retrieve(string $resourceClass, $id, array $context);

    /**
     * Retrieves a list of resources with some pagination.
     *
     * @param string $resourceClass
     * @param array $context
     * @param SearchFilterRequest $searchFilterRequest
     * @return iterable
     */
    public function retrieveAll(string $resourceClass, array $context, SearchFilterRequest $searchFilterRequest): iterable;
}
