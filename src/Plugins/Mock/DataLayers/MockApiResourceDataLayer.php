<?php

namespace W2w\Lib\Apie\Plugins\Mock\DataLayers;

use Pagerfanta\Pagerfanta;
use Psr\Cache\CacheItemPoolInterface;
use ReflectionClass;
use W2w\Lib\Apie\Core\IdentifierExtractor;
use W2w\Lib\Apie\Core\SearchFilters\SearchFilterFromMetadataTrait;
use W2w\Lib\Apie\Core\SearchFilters\SearchFilterRequest;
use W2w\Lib\Apie\Exceptions\ResourceNotFoundException;
use W2w\Lib\Apie\Interfaces\ApiResourcePersisterInterface;
use W2w\Lib\Apie\Interfaces\ApiResourceRetrieverInterface;
use W2w\Lib\Apie\Interfaces\SearchFilterProviderInterface;
use W2w\Lib\Apie\Plugins\Mock\Pagers\MockAdapter;
use W2w\Lib\ApieObjectAccessNormalizer\ObjectAccess\ObjectAccessInterface;

/**
 * If the implementation of a REST API is mocked this is the class that persists and retrieves all API resources.
 *
 * It does this by persisting it with a cache pool.
 */
class MockApiResourceDataLayer implements ApiResourcePersisterInterface, ApiResourceRetrieverInterface, SearchFilterProviderInterface
{
    use SearchFilterFromMetadataTrait;

    /**
     * @var CacheItemPoolInterface
     */
    private $cacheItemPool;

    /**
     * @var IdentifierExtractor
     */
    private $identifierExtractor;

    /**
     * @var ObjectAccessInterface
     */
    private $objectAccess;

    public function __construct(
        CacheItemPoolInterface $cacheItemPool,
        IdentifierExtractor $identifierExtractor,
        ObjectAccessInterface $objectAccess
    ) {
        $this->cacheItemPool = $cacheItemPool;
        $this->identifierExtractor = $identifierExtractor;
        $this->objectAccess = $objectAccess;
    }

    /**
     * @param mixed $resource
     * @param array $context
     * @return mixed
     */
    public function persistNew($resource, array $context = [])
    {
        $id = $this->identifierExtractor->getIdentifierValue($resource, $context);
        if (is_null($id)) {
            return $resource;
        }

        $this->persist($resource, $id);
        return $resource;
    }

    /**
     * @param mixed $resource
     * @param string|int $int
     * @param array $context
     * @return mixed
     */
    public function persistExisting($resource, $int, array $context = [])
    {
        $this->persist($resource, $int);
        return $resource;
    }

    private function persist($resource, $id)
    {
        $cacheKey = 'mock-server.' . $this->shortName($resource) . '.' . $id;
        $cacheItem = $this->cacheItemPool->getItem($cacheKey)->set(serialize($resource));
        $this->addId(get_class($resource), $id);
        $this->cacheItemPool->save($cacheItem);
        $this->cacheItemPool->commit();
    }

    /**
     * @param string $resourceClass
     * @param string|int $id
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
     * @param string|int $id
     * @param array $context
     * @return mixed
     */
    public function retrieve(string $resourceClass, $id, array $context)
    {
        $cacheKey = 'mock-server.' . $this->shortName($resourceClass) . '.' . $id;
        $cacheItem = $this->cacheItemPool->getItem($cacheKey);
        if (!$cacheItem->isHit()) {
            throw new ResourceNotFoundException((string) $id);
        }
        return unserialize($cacheItem->get());
    }

    /**
     * @param string $resourceClass
     * @param array $context
     * @param SearchFilterRequest $searchFilterRequest
     * @return Pagerfanta|array
     */
    public function retrieveAll(string $resourceClass, array $context, SearchFilterRequest $searchFilterRequest): iterable
    {
        $cacheKey = 'mock-server-all.' . $this->shortName($resourceClass);
        $cacheItem = $this->cacheItemPool->getItem($cacheKey);
        if (!$cacheItem->isHit()) {
            return [];
        }
        $paginator = new Pagerfanta(new MockAdapter($this, $cacheItem->get(), $searchFilterRequest->getSearches(), $resourceClass, $context, $this->objectAccess));
        $searchFilterRequest->updatePaginator($paginator);
        return $paginator;
    }

    /**
     * Marks an id as found, so the get all can retrieve it.
     *
     * @param string $resourceClass
     * @param string|int $id
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
     * @param string|int $id
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
     * @param mixed $resourceOrResourceClass
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
