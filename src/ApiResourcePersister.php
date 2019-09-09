<?php

namespace W2w\Lib\Apie;

use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use UnexpectedValueException;

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
            throw new MethodNotAllowedHttpException([], '"Resource has no post support"');
        }
        $result = $metadata->getResourcePersister()
            ->persistNew($resource, $metadata->getContext());
        if (!$result instanceof $resourceClass) {
            throw new UnexpectedValueException('I expect the class ' . get_class($metadata->getResourcePersister()) . ' to return an instance of ' . $resourceClass . ' but got ' . $this->getType($result));
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
            throw new MethodNotAllowedHttpException([], '"Resource has no put support"');
        }

        $result = $metadata->getResourcePersister()
            ->persistExisting($resource, $id, $metadata->getContext());
        if (!$result instanceof $resourceClass) {
            throw new UnexpectedValueException('I expect the class ' . get_class($metadata->getResourcePersister()) . ' to return an instance of ' . $resourceClass . ' but got ' . $this->getType($result));
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
            throw new MethodNotAllowedHttpException([], '"Resource has no delete support"');
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
