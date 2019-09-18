<?php
namespace W2w\Lib\Apie\Persisters;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use W2w\Lib\Apie\Exceptions\CanNotDetermineIdException;
use W2w\Lib\Apie\Exceptions\ResourceNotFoundException;
use W2w\Lib\Apie\Retrievers\ApiResourceRetrieverInterface;

/**
 * Persists and retrieves from an array in memory. Only useful for unit tests.
 */
class ArrayPersister implements ApiResourcePersisterInterface, ApiResourceRetrieverInterface
{
    private $propertyAccessor;

    private $persisted = [];

    public function __construct(PropertyAccessor $propertyAccessor = null)
    {
        $this->propertyAccessor = $propertyAccessor ?? PropertyAccess::createPropertyAccessorBuilder()->getPropertyAccessor();
    }
    /**
     * Persist a new API resource. Should return the new API resource.
     *
     * @param mixed $resource
     * @param array $context
     * @return mixed
     */
    public function persistNew($resource, array $context = [])
    {
        $className = get_class($resource);
        $identifier = $context['identifier'] ?? 'id';
        $keepReference = $context['keep_reference'] ?? false;
        if (!$this->propertyAccessor->isReadable($resource, $identifier)) {
            throw new CanNotDetermineIdException($resource, $identifier);
        }
        $id = $this->propertyAccessor->getValue($resource, $identifier);

        if (empty($this->persisted[$className])) {
            $this->persisted[$className] = [];
        }
        if (!$keepReference) {
            $resource = clone $resource;
        }
        $this->persisted[$className][$id] = $resource;
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
        $className = get_class($resource);
        $keepReference = $context['keep_reference'] ?? false;
        if (empty($this->persisted[$className])) {
            $this->persisted[$className] = [];
        }
        if (!$keepReference) {
            $resource = clone $resource;
        }
        $this->persisted[$className][$int] = $resource;
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
        if (!empty($this->persisted[$resourceClass][$id])) {
            unset($this->persisted[$resourceClass][$id]);
        }
    }

    /**
     * Retrieves a single resource by some identifier.
     *
     * @param string $resourceClass
     * @param mixed $id
     * @param array $context
     * @return mixed
     */
    public function retrieve(string $resourceClass, $id, array $context)
    {
        if (empty($this->persisted[$resourceClass][$id])) {
            throw new ResourceNotFoundException($id);
        }
        return $this->persisted[$resourceClass][$id];
    }

    /**
     * Retrieves a list of resources with some pagination.
     *
     * @param string $resourceClass
     * @param array $context
     * @param int $pageIndex
     * @param int $numberOfItems
     * @return iterable
     */
    public function retrieveAll(string $resourceClass, array $context, int $pageIndex, int $numberOfItems): iterable
    {
        if (empty($this->persisted[$resourceClass])) {
            return [];
        }
        return array_slice(array_values($this->persisted[$resourceClass]), $pageIndex * $numberOfItems, $numberOfItems);
    }
}
