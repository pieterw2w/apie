<?php

namespace W2w\Lib\Apie\Mocks;

use Psr\Cache\CacheItemPoolInterface;
use ReflectionClass;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use W2w\Lib\Apie\Exceptions\ResourceNotFoundException;
use W2w\Lib\Apie\Persisters\ApiResourcePersisterInterface;
use W2w\Lib\Apie\Retrievers\ApiResourceRetrieverInterface;

/**
 * If the implementation of a REST API is mocked this is the class that persists and retrieves all API resources.
 *
 * It does this by persisting it with a cache pool.
 */
class MockApiResourceRetriever implements ApiResourcePersisterInterface, ApiResourceRetrieverInterface
{
    private $cacheItemPool;

    private $propertyAccessor;

    public function __construct(
        CacheItemPoolInterface $cacheItemPool,
        PropertyAccessor $propertyAccessor
    ) {
        $this->cacheItemPool = $cacheItemPool;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * @param $resource
     * @param array $context
     * @return mixed
     */
    public function persistNew($resource, array $context = [])
    {
        $id = null;
        if ($this->propertyAccessor->isReadable($resource, 'id')) {
            $id = $this->propertyAccessor->getValue($resource, 'id');
        } elseif ($this->propertyAccessor->isReadable($resource, 'uuid')) {
            $id = $this->propertyAccessor->getValue($resource, 'uuid');
        }
        if (is_null($id)) {
            return;
        }

        $cacheKey = 'mock-server.' . $this->shortName($resource) . '.' . $id;
        $cacheItem = $this->cacheItemPool->getItem($cacheKey)->set(serialize($resource));
        $this->addId(get_class($resource), $id);
        $this->cacheItemPool->save($cacheItem);
        $this->cacheItemPool->commit();
        return $resource;
    }

    /**
     * @param $resource
     * @param $int
     * @param array $context
     * @return mixed
     */
    public function persistExisting($resource, $int, array $context = [])
    {
        $cacheKey = 'mock-server.' . $this->shortName($resource) . '.' . $int;
        $cacheItem = $this->cacheItemPool->getItem($cacheKey)->set(serialize($resource));
        $this->addId(get_class($resource), $int);
        $this->cacheItemPool->save($cacheItem);
        $this->cacheItemPool->commit();
        return $resource;
    }

    /**
     * @param string $resourceClass
     * @param $id
     * @param array $context
     */
    public function remove(string $resourceClass, $id, array $context)
    {
        $cacheKey = 'mock-server.' . $this->shortName($resourceClass) . '.' . $id;
        $this->cacheItemPool->deleteItem($cacheKey);
        $this->removeId($resourceClass, $id);
        $this->cacheItemPool->commit();
    }

    /**
     * @param string $resourceClass
     * @param $id
     * @param array $context
     * @return mixed
     */
    public function retrieve(string $resourceClass, $id, array $context)
    {
        $cacheKey = 'mock-server.' . $this->shortName($resourceClass) . '.' . $id;
        $cacheItem = $this->cacheItemPool->getItem($cacheKey);
        if (!$cacheItem->isHit()) {
            throw new ResourceNotFoundException($id);
        }
        return unserialize($cacheItem->get());
    }

    /**
     * @param string $resourceClass
     * @param array $context
     * @param int $pageIndex
     * @param int $numberOfItems
     * @return iterable
     */
    public function retrieveAll(string $resourceClass, array $context, int $pageIndex, int $numberOfItems): iterable
    {
        $cacheKey = 'mock-server-all.' . $this->shortName($resourceClass);
        $cacheItem = $this->cacheItemPool->getItem($cacheKey);
        if (!$cacheItem->isHit()) {
            return [];
        }
        $ids = array_slice($cacheItem->get(), $pageIndex * $numberOfItems, $numberOfItems);

        return array_values(array_map(function ($id) use ($resourceClass, &$context) {
            return $this->retrieve($resourceClass, $id, $context);
        }, $ids));
    }

    /**
     * Marks an id as found, so the get all can retrieve it.
     *
     * @param string $resourceClass
     * @param $id
     */
    private function addId(string $resourceClass, $id)
    {
        $cacheKey = 'mock-server-all.' . $this->shortName($resourceClass);
        $cacheItem = $this->cacheItemPool->getItem($cacheKey);
        $ids = [];
        if ($cacheItem->isHit()) {
            $ids = $cacheItem->get();
        }
        $ids[$id] = $id;
        $this->cacheItemPool->save($cacheItem->set($ids));
    }

    /**
     * Marks an id as not found, so the get all will no longer retrieve it.
     *
     * @param string $resourceClass
     * @param $id
     */
    private function removeId(string $resourceClass, $id)
    {
        $cacheKey = 'mock-server-all.' . $this->shortName($resourceClass);
        $cacheItem = $this->cacheItemPool->getItem($cacheKey);
        $ids = [];
        if ($cacheItem->isHit()) {
            $ids = $cacheItem->get();
        }
        unset($ids[$id]);
        $this->cacheItemPool->save($cacheItem->set($ids));
    }

    /**
     * Returns a short name of a resource or a resource class.
     *
     * @param $resourceOrResourceClass
     * @return string
     */
    private function shortName($resourceOrResourceClass): string
    {
        if (is_string($resourceOrResourceClass)) {
            $refl = new ReflectionClass($resourceOrResourceClass);

            return $refl->getShortName();
        }

        return $this->shortName(get_class($resourceOrResourceClass));
    }
}
