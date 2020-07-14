<?php
namespace W2w\Test\Apie\Plugins\Mock\ResourceFactories;

use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Core\SearchFilters\SearchFilterRequest;
use W2w\Lib\Apie\Interfaces\ApiResourceFactoryInterface;
use W2w\Lib\Apie\Interfaces\ApiResourcePersisterInterface;
use W2w\Lib\Apie\Interfaces\ApiResourceRetrieverInterface;
use W2w\Lib\Apie\Plugins\Core\DataLayers\MemoryDataLayer;
use W2w\Lib\Apie\Plugins\Mock\DataLayers\MockApiResourceDataLayer;
use W2w\Lib\Apie\Plugins\Mock\ResourceFactories\MockApiResourceFactory;

class MockApiResourceFactoryTest extends TestCase
{
    private $skippedRetriever;

    private $skippedPersister;

    private $testItem;

    private $retriever;

    private $factory;

    protected function setUp(): void
    {
        $this->skippedRetriever = new class implements ApiResourceRetrieverInterface
        {
            public function retrieve(string $resourceClass, $id, array $context)
            {
                // TODO: Implement retrieve() method.
            }

            public function retrieveAll(string $resourceClass, array $context, SearchFilterRequest $searchFilterRequest
            ): iterable {
                // TODO: Implement retrieveAll() method.
            }
        };
        $this->skippedPersister = new class implements ApiResourcePersisterInterface
        {
            public function persistNew($resource, array $context = [])
            {
                // TODO: Implement persistNew() method.
            }

            public function persistExisting($resource, $int, array $context = [])
            {
                // TODO: Implement persistExisting() method.
            }

            public function remove(string $resourceClass, $id, array $context)
            {
                // TODO: Implement remove() method.
            }
        };

        $this->retriever = $this->prophesize(MockApiResourceDataLayer::class);
        $this->factory = $this->prophesize(ApiResourceFactoryInterface::class);
        $this->testItem = new MockApiResourceFactory(
            $this->retriever->reveal(),
            $this->factory->reveal(),
            [get_class($this->skippedRetriever), get_class($this->skippedPersister)]
        );
    }

    public function testGetApiResourceRetrieverInstance()
    {
        $retriever = $this->prophesize(ApiResourceRetrieverInterface::class)->reveal();
        $this->factory->getApiResourceRetrieverInstance('donut-retriever')->shouldBeCalled()->willReturn($retriever);

        $this->assertInstanceOf(
            MockApiResourceDataLayer::class,
            $this->testItem->getApiResourceRetrieverInstance('donut-retriever')
        );
    }

    public function testGetApiResourceRetrieverInstance_skipped_resource()
    {
        $this->factory->getApiResourceRetrieverInstance('donut-retriever')->shouldBeCalled()->willReturn($this->skippedRetriever);

        $this->assertEquals(
            $this->skippedRetriever,
            $this->testItem->getApiResourceRetrieverInstance('donut-retriever')
        );
    }

    public function testGetApiResourcePersisterInstance()
    {
        $persister = $this->prophesize(ApiResourcePersisterInterface::class)->reveal();
        $this->factory->getApiResourcePersisterInstance('donut-retriever')->shouldBeCalled()->willReturn($persister);

        $this->assertInstanceOf(
            MockApiResourceDataLayer::class,
            $this->testItem->getApiResourcePersisterInstance('donut-retriever')
        );
    }

    public function testGetApiResourcePersisterInstance_skipped_resource()
    {
        $this->factory->getApiResourcePersisterInstance('donut-retriever')->shouldBeCalled()->willReturn($this->skippedPersister);

        $this->assertEquals(
            $this->skippedPersister,
            $this->testItem->getApiResourcePersisterInstance('donut-retriever')
        );
    }
}
