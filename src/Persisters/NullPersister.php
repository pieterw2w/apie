<?php
namespace W2w\Lib\Apie\Persisters;

/**
 * Persists nothing. Only created for entities that require POST, but do not need any storage.
 */
class NullPersister implements ApiResourcePersisterInterface
{

    /**
     * Persist a new API resource. Should return the new API resource.
     *
     * @param mixed $resource
     * @param array $context
     * @return mixed
     */
    public function persistNew($resource, array $context = [])
    {
        return $resource;
    }

    /**
     * Persist an existing API resource. The input resource is the modified API resource. Should return the new API
     * resource.
     *
     * @param $resource
     * @param $int
     * @param array $context
     * @return mixed
     */
    public function persistExisting($resource, $int, array $context = [])
    {
        return $resource;
    }

    /**
     * Removes an existing API resource.
     *
     * @param string $resourceClass
     * @param $id
     * @param array $context
     * @return mixed
     */
    public function remove(string $resourceClass, $id, array $context)
    {
    }
}
