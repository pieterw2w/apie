<?php

namespace W2w\Lib\Apie;

use W2w\Lib\Apie\Exceptions\InvalidClassTypeException;
use W2w\Lib\Apie\Exceptions\InvalidReturnTypeOfApiResourceException;
use W2w\Lib\Apie\Exceptions\MethodNotAllowedException;

/**
 * Class that does the persist action by reading the metadata of an Api resource.
 */
class ApiResourcePersister
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
     * Persist a new resource.
     *
     * @param $resource
     * @return mixed
     */
    public function persistNew($resource)
    {
        $resourceClass = get_class($resource);
        $metadata = $this->factory->getMetadata($resourceClass);
        if (!$metadata->allowPost()) {
            throw new MethodNotAllowedException('post');
        }
        $result = $metadata->getResourcePersister()
            ->persistNew($resource, $metadata->getContext());
        if (!$result instanceof $resourceClass) {
            throw new InvalidReturnTypeOfApiResourceException($metadata->getResourcePersister(), $this->getType($result), $resourceClass);
        }

        return $result;
    }

    /**
     * Persist an existing resource.
     *
     * @param $resource
     * @param $id
     * @return mixed
     */
    public function persistExisting($resource, $id)
    {
        $resourceClass = get_class($resource);
        $metadata = $this->factory->getMetadata($resourceClass);
        if (!$metadata->allowPut()) {
            throw new MethodNotAllowedException('put');
        }

        $result = $metadata->getResourcePersister()
            ->persistExisting($resource, $id, $metadata->getContext());
        if (!$result instanceof $resourceClass) {
            throw new InvalidReturnTypeOfApiResourceException($metadata->getResourcePersister(), $this->getType($result), $resourceClass);
        }

        return $result;
    }

    /**
     * Removes an existing resource.
     *
     * @param string $resourceClass
     * @param $id
     */
    public function delete(string $resourceClass, $id)
    {
        $metadata = $this->factory->getMetadata($resourceClass);
        if (!$metadata->allowDelete()) {
            throw new MethodNotAllowedException('delete');
        }

        $metadata->getResourcePersister()->remove($resourceClass, $id, $metadata->getContext());
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
