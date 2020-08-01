<?php
namespace W2w\Lib\Apie\Plugins\FileStorage\DataLayers;

use LimitIterator;
use Pagerfanta\Pagerfanta;
use ReflectionClass;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use W2w\Lib\Apie\Core\SearchFilters\SearchFilterFromMetadataTrait;
use W2w\Lib\Apie\Core\SearchFilters\SearchFilterRequest;
use W2w\Lib\Apie\Exceptions\CanNotDetermineIdException;
use W2w\Lib\Apie\Exceptions\CouldNotMakeDirectoryException;
use W2w\Lib\Apie\Exceptions\CouldNotRemoveFileException;
use W2w\Lib\Apie\Exceptions\CouldNotWriteFileException;
use W2w\Lib\Apie\Exceptions\InvalidIdException;
use W2w\Lib\Apie\Exceptions\ResourceNotFoundException;
use W2w\Lib\Apie\Interfaces\ApiResourcePersisterInterface;
use W2w\Lib\Apie\Interfaces\ApiResourceRetrieverInterface;
use W2w\Lib\Apie\Interfaces\SearchFilterProviderInterface;
use W2w\Lib\Apie\Plugins\FileStorage\Pagers\FilestoragePager;

class FileStorageDataLayer implements ApiResourcePersisterInterface, ApiResourceRetrieverInterface, SearchFilterProviderInterface
{
    use SearchFilterFromMetadataTrait;

    private $folder;

    private $propertyAccessor;

    public function __construct(string $folder, PropertyAccessor $propertyAccessor)
    {
        $this->folder = $folder;
        $this->propertyAccessor = $propertyAccessor;
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
        $identifier = $context['identifier'] ?? 'id';
        if (!$this->propertyAccessor->isReadable($resource, $identifier)) {
            throw new CanNotDetermineIdException($resource, $identifier);
        }
        $id = $this->propertyAccessor->getValue($resource, $identifier);
        $this->store($resource, $id);
        return $resource;

    }

    /**
     * Persist an existing API resource. The input resource is the modified API resource. Should return the new API
     * resource.
     *
     * @param mixed $resource
     * @param mixed $int
     * @param array $context
     * @return mixed
     */
    public function persistExisting($resource, $int, array $context = [])
    {
        $identifier = $context['identifier'] ?? 'id';
        if ($this->propertyAccessor->isReadable($resource, $identifier)) {
            $actualIdentifier = $this->propertyAccessor->getValue($resource, $identifier);
            if ((string) $actualIdentifier !== (string) $int) {
                throw new InvalidIdException((string) $int);
            }
        }
        $this->store($resource, $int);
        return $resource;
    }

    /**
     * Removes an existing API resource.
     *
     * @param string $resourceClass
     * @param string|int $id
     * @param array $context
     * @return mixed
     */
    public function remove(string $resourceClass, $id, array $context)
    {
        $file = $this->getFilename($resourceClass, $id);
        if (!@unlink($file)) {
            throw new CouldNotRemoveFileException($file);
        }
    }

    /**
     * Retrieves a single resource by some identifier.
     *
     * @param string $resourceClass
     * @param string|int $id
     * @param array $context
     * @return mixed
     */
    public function retrieve(string $resourceClass, $id, array $context)
    {
        $file = $this->getFilename($resourceClass, $id);
        if (!file_exists($file)) {
            throw new ResourceNotFoundException($id);
        }
        return unserialize(file_get_contents($file));
    }

    /**
     * Retrieves a list of resources with some pagination.
     *
     * @param string $resourceClass
     * @param array $context
     * @param SearchFilterRequest $searchFilterRequest
     * @return Pagerfanta
     */
    public function retrieveAll(string $resourceClass, array $context, SearchFilterRequest $searchFilterRequest): iterable
    {
        $folder = $this->getFolder($resourceClass);
        $iterator = Finder::create()->files()->sortByName()->depth(0)->in($folder)->getIterator();
        $paginator = new Pagerfanta(new FilestoragePager($this, $iterator, $resourceClass, $context));
        $searchFilterRequest->updatePaginator($paginator);
        return $paginator;
    }

    protected function getFolder(string $resourceClass): string
    {
        $refl = new ReflectionClass($resourceClass);
        $folder = $this->folder . DIRECTORY_SEPARATOR . $refl->getShortName();
        if (!is_dir($folder)) {
            if (!@mkdir($folder, 0777, true)) {
                throw new CouldNotMakeDirectoryException($folder);
            };
        }
        return $folder;
    }

    protected function getFilename(string $resourceClass, string $id): string
    {
        if (!preg_match('/^[a-zA-Z0-9_.-]+$/', $id)) {
            throw new InvalidIdException($id);
        }
        $folder = $this->getFolder($resourceClass);

        return $folder . DIRECTORY_SEPARATOR . $id;

    }

    private function store($resource, string $id) {
        $filename = $this->getFilename(get_class($resource), $id);
        if (false === file_put_contents($filename, serialize($resource))) {
            throw new CouldNotWriteFileException($filename);
        };
    }
}
