<?php

namespace W2w\Lib\Apie;

use W2w\Lib\Apie\Exceptions\InvalidReturnTypeOfApiResourceException;
use W2w\Lib\Apie\Exceptions\MethodNotAllowedException;
use W2w\Lib\Apie\Retrievers\SearchFilterProviderInterface;
use W2w\Lib\Apie\SearchFilters\SearchFilterRequest;

/**
 * Class that does the action to retrieve an Api resource.
 */
class ApiResourceRetriever
{
    /**
     * @var ApiResourceMetadataFactory
     */
    private $factory;

    /**
     * @param ApiResourceMetadataFactory $factory
     */
    public function __construct(ApiResourceMetadataFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param string $resourceClass
     * @param string|int $id
     * @return mixed
     */
    public function retrieve(string $resourceClass, $id)
    {
        $metadata = $this->factory->getMetadata($resourceClass);
        if (!$metadata->allowGet()) {
            throw new MethodNotAllowedException('get $id');
        }
        $result = $metadata->getResourceRetriever()
            ->retrieve($resourceClass, $id, $metadata->getContext());
        if (!$result instanceof $resourceClass) {
            throw new InvalidReturnTypeOfApiResourceException($metadata->getResourceRetriever(), $this->getType($result), $resourceClass);
        }

        return $result;
    }

    /**
     * @param string $resourceClass
     * @param SearchFilterRequest|null $searchFilterRequest
     * @return iterable
     */
    public function retrieveAll(string $resourceClass, ?SearchFilterRequest $searchFilterRequest = null)
    {
        if (!$searchFilterRequest) {
            $searchFilterRequest = new SearchFilterRequest();
        }
        $metadata = $this->factory->getMetadata($resourceClass);
        if (!$metadata->allowGetAll()) {
            throw new MethodNotAllowedException('get all');
        }
        if (!$metadata->hasResourceRetriever()) {
            // Many OpenAPI generators expect the get all call to be working at all times.
            return [];
        }
        $retriever = $metadata->getResourceRetriever();
        if ($retriever instanceof SearchFilterProviderInterface) {
            $searchFilterRequest = $searchFilterRequest->applySearchFilter($retriever->getSearchFilter($metadata));
        }

        $result = $retriever->retrieveAll($resourceClass, $metadata->getContext(), $searchFilterRequest);
        foreach ($result as $instance) {
            if (!$instance instanceof $resourceClass) {
                throw new InvalidReturnTypeOfApiResourceException($metadata->getResourceRetriever(), $this->getType($instance), $resourceClass);
            }
        }

        return $result;
    }

    /**
     * Returns a type display of an object instance.
     *
     * @param mixed $object
     * @return string
     */
    private function getType($object)
    {
        if (is_object($object)) {
            return get_class($object);
        }
        if (is_string($object)) {
            return 'string ' . json_encode($object);
        }

        return gettype($object);
    }
}
