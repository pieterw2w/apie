<?php

namespace W2w\Lib\Apie;

use W2w\Lib\Apie\Exceptions\InvalidReturnTypeOfApiResourceException;
use W2w\Lib\Apie\Exceptions\MethodNotAllowedException;

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
     * @param $id
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
     * @param int $pageIndex
     * @param int $numberOfItems
     * @return iterable
     */
    public function retrieveAll(string $resourceClass, int $pageIndex, int $numberOfItems)
    {
        $metadata = $this->factory->getMetadata($resourceClass);
        if (!$metadata->allowGetAll()) {
            throw new MethodNotAllowedException('get all');
        }
        if (!$metadata->hasResourceRetriever()) {
            // Many OpenAPI generators expect the get all call to be working at all times.
            return [];
        }
        $result = $metadata->getResourceRetriever()
            ->retrieveAll($resourceClass, $metadata->getContext(), $pageIndex, $numberOfItems);
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
     * @param $object
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
