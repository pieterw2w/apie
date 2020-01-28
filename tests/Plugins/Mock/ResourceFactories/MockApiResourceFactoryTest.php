<?php
namespace W2w\Test\Apie\Plugins\Mock\ResourceFactories;

use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Interfaces\ApiResourceFactoryInterface;
use W2w\Lib\Apie\Interfaces\ApiResourcePersisterInterface;
use W2w\Lib\Apie\Interfaces\ApiResourceRetrieverInterface;
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
        $this->skippedRetriever = $this->prophesize(ApiResourceRetrieverInterface::class)->reveal();
        $this->skippedPersister = $this->prophesize(ApiResourcePersisterInterface::class)->reveal();

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

        $this->assertEquals(
            $this->retriever->reveal(),
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

        $this->assertEquals(
            $this->retriever->reveal(),
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
