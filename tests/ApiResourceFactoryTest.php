<?php
namespace W2w\Test\Apie;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use W2w\Lib\Apie\ApiResourceFactory;
use W2w\Lib\Apie\Exceptions\CouldNotConstructApiResourceClassException;
use W2w\Lib\Apie\Exceptions\InvalidClassTypeException;
use W2w\Lib\Apie\Persisters\ApiResourcePersisterInterface;
use W2w\Lib\Apie\Persisters\NullPersister;
use W2w\Lib\Apie\Retrievers\ApiResourceRetrieverInterface;
use W2w\Lib\Apie\Retrievers\FileStorageDataLayer;
use W2w\Lib\Apie\Retrievers\StatusCheckRetriever;
use W2w\Lib\Apie\SearchFilters\SearchFilterRequest;

class ApiResourceFactoryTest extends TestCase
{
    private $testItem;

    private $testItemNoContainer;

    protected function setUp(): void
    {
        $container = new class implements ContainerInterface
        {
            public function get($id)
            {
                switch ($id) {
                    case 'status-check':
                    case StatusCheckRetriever::class:
                        return new StatusCheckRetriever([]);
                }
                return new NullPersister();
            }

            public function has($id)
            {
                return $id === StatusCheckRetriever::class || $id === 'status-check' || $id === NullPersister::class;

            }
        };
        $this->testItem = new ApiResourceFactory($container);
        $this->testItemNoContainer = new ApiResourceFactory();
    }

    public function testGetApiResourceRetrieverInstance()
    {
        $this->assertInstanceOf(
            StatusCheckRetriever::class,
            $this->testItem->getApiResourceRetrieverInstance(StatusCheckRetriever::class)
        );
        $dummy = new class implements ApiResourceRetrieverInterface
        {
            public function retrieve(string $resourceClass, $id, array $context)
            {
            }

            public function retrieveAll(string $resourceClass, array $context, SearchFilterRequest $searchFilterRequest): iterable
            {
                return [];
            }
        };
        $this->assertInstanceOf(
            get_class($dummy),
            $this->testItem->getApiResourceRetrieverInstance(get_class($dummy))
        );
    }

    public function testGetApiResourcePersisterInstance()
    {
        $this->assertInstanceOf(
            NullPersister::class,
            $this->testItem->getApiResourcePersisterInstance(NullPersister::class)
        );
        $dummy = new class implements ApiResourcePersisterInterface
        {
            public function persistNew($resource, array $context = [])
            {
            }

            public function persistExisting($resource, $int, array $context = [])
            {
            }

            public function remove(string $resourceClass, $id, array $context)
            {
            }
        };
        $this->assertInstanceOf(
            get_class($dummy),
            $this->testItem->getApiResourcePersisterInstance(get_class($dummy))
        );
    }

    public function testGetApiResourceRetrieverInstance_class_not_exists()
    {
        $this->expectException(CouldNotConstructApiResourceClassException::class);
        $this->testItem->getApiResourceRetrieverInstance('ClassNotExist');
    }

    public function testGetApiResourceRetrieverInstance_class_has_constructor()
    {
        $this->expectException(CouldNotConstructApiResourceClassException::class);
        $this->testItem->getApiResourceRetrieverInstance(FileStorageDataLayer::class);
    }

    public function testGetApiResourceRetrieverInstance_class_invalid_type()
    {
        $this->expectException(InvalidClassTypeException::class);
        $this->testItem->getApiResourceRetrieverInstance(NullPersister::class);
    }

    public function testGetApiResourcePersisterInstance_class_not_exists()
    {
        $this->expectException(CouldNotConstructApiResourceClassException::class);
        $this->testItem->getApiResourcePersisterInstance('ClassNotExist');
    }

    public function testGetApiResourcePersisterInstance_class_has_constructor()
    {
        $this->expectException(CouldNotConstructApiResourceClassException::class);
        $this->testItem->getApiResourcePersisterInstance(FileStorageDataLayer::class);
    }

    public function testGetApiResourcePersisterInstance_class_invalid_type()
    {
        $this->expectException(InvalidClassTypeException::class);
        $this->testItem->getApiResourcePersisterInstance(StatusCheckRetriever::class);
    }
}
