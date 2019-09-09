<?php

namespace W2w\Lib\Apie\Retrievers;

/**
 * Interface for a service to retrieve an api resource by an id.
 *
 * @TODO: add search filters for retrieveAll
 * @TODO: add a total amount for retrieveAll so we can send metadata how many records there are..
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
     * @param int $pageIndex
     * @param int $numberOfItems
     * @return iterable
     */
    public function retrieveAll(string $resourceClass, array $context, int $pageIndex, int $numberOfItems): iterable;
}
