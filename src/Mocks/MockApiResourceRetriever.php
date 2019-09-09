<?php

namespace W2w\Lib\Apie\Mocks;

use W2w\Lib\Apie\Normalizers\ContextualNormalizer;
use W2w\Lib\Apie\Normalizers\EvilReflectionPropertyNormalizer;
use W2w\Lib\Apie\Persisters\ApiResourcePersisterInterface;
use W2w\Lib\Apie\Retrievers\ApiResourceRetrieverInterface;
use Psr\Cache\CacheItemPoolInterface;
use Ramsey\Uuid\Uuid;
use ReflectionClass;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * If the implementation of a REST API is mocked this is the class that persists and retrieves all API resources.
 *
 * It does this by persisting it with a cache pool.
 */
class MockApiResourceRetriever implements ApiResourcePersisterInterface, ApiResourceRetrieverInterface
{
    private $cacheItemPool;

    private $normalizer;

    private $denormalizer;

    public function __construct(
        CacheItemPoolInterface $cacheItemPool,
        NormalizerInterface $normalizer,
        DenormalizerInterface $denormalizer
    ) {
        $this->cacheItemPool = $cacheItemPool;
        $this->normalizer = $normalizer;
        $this->denormalizer = $denormalizer;
    }

    /**
     * @param $resource
     * @param array $context
     * @return mixed
     */
    public function persistNew($resource, array $context = [])
    {
        $normalizedData = $this->normalizer->normalize($resource);
        if (!isset($normalizedData['id'])) {
            $normalizedData['id'] = (string) Uuid::uuid4();
        }
        $this->denormalize($normalizedData, $resource);
        $cacheKey = 'mock-server.' . $this->shortName($resource) . '.' . $normalizedData['id'];
        $cacheItem = $this->cacheItemPool->getItem($cacheKey)->set($normalizedData);
        $this->addId(get_class($resource), $normalizedData['id']);
        $this->cacheItemPool->save($cacheItem);

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
        $normalizedData = $this->normalizer->normalize($resource);
        if (!isset($normalizedData['id'])) {
            $normalizedData['id'] = $int;
        }
        $this->denormalize($normalizedData, $resource);
        $cacheKey = 'mock-server.' . $this->shortName($resource) . '.' . $int;
        $cacheItem = $this->cacheItemPool->getItem($cacheKey)->set($normalizedData);
        $this->addId(get_class($resource), $int);
        $this->cacheItemPool->save($cacheItem);

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
            throw new HttpException(404, $id . ' not found!');
        }
        ContextualNormalizer::enableDenormalizer(EvilReflectionPropertyNormalizer::class);
        try {
            $res = $this->denormalizer->denormalize($cacheItem->get(), $resourceClass);
        } finally {
            ContextualNormalizer::disableDenormalizer(EvilReflectionPropertyNormalizer::class);
        }

        return $res;
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

        return array_map(function ($id) use ($resourceClass) {
            return $this->retrieve($resourceClass, $id);
        }, $ids);
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
        $ids[] = $id;
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
        $ids = array_filter($ids, function ($foundId) use (&$id) {
            return $foundId !== $id;
        });
        $this->cacheItemPool->save($cacheItem->set($ids));
    }

    /**
     * Denormalize an existing resource with data from the cache.
     *
     * @param array $array
     * @param $resource
     * @return mixed
     */
    private function denormalize(array $array, $resource)
    {
        ContextualNormalizer::enableDenormalizer(EvilReflectionPropertyNormalizer::class);
        try {
            $res = $this->denormalizer->denormalize($array, get_class($resource), null, ['object_to_populate' => $resource]);
        } finally {
            ContextualNormalizer::disableDenormalizer(EvilReflectionPropertyNormalizer::class);
        }

        return $res;
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
