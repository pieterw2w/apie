<?php

namespace W2w\Test\Apie\Core\ResourceFactories;

use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Core\ResourceFactories\ChainableFactory;
use W2w\Lib\Apie\Exceptions\CouldNotConstructApiResourceClassException;
use W2w\Lib\Apie\Interfaces\ApiResourceFactoryInterface;
use W2w\Lib\Apie\Interfaces\ApiResourcePersisterInterface;
use W2w\Lib\Apie\Interfaces\ApiResourceRetrieverInterface;
use W2w\Lib\Apie\Plugins\Core\DataLayers\MemoryDataLayer;
use W2w\Lib\Apie\Plugins\Core\DataLayers\NullDataLayer;
use W2w\Lib\Apie\Plugins\FileStorage\DataLayers\FileStorageDataLayer;

class ChainableFactoryTest extends TestCase
{
    private $testItem;

    protected function setUp(): void
    {
        $factory1 = new class implements ApiResourceFactoryInterface {
            public $identifier;

            public function hasApiResourceRetrieverInstance(string $identifier): bool
            {
                return $identifier === $this->identifier;
            }

            public function getApiResourceRetrieverInstance(string $identifier): ApiResourceRetrieverInterface
            {
                return new $identifier();
            }

            public function hasApiResourcePersisterInstance(string $identifier): bool
            {
                return $identifier === $this->identifier;
            }

            public function getApiResourcePersisterInstance(string $identifier): ApiResourcePersisterInterface
            {
                return new $identifier();
            }
        };
        $factory2 = clone $factory1;

        $factory1->identifier = NullDataLayer::class;
        $factory2->identifier = MemoryDataLayer::class;
        $this->testItem = new ChainableFactory([$factory1, $factory2]);
    }

    public function testGetApiResourceRetrieverInstance()
    {
        $this->assertFalse($this->testItem->hasApiResourceRetrieverInstance(FileStorageDataLayer::class));
        $this->assertTrue($this->testItem->hasApiResourceRetrieverInstance(MemoryDataLayer::class));
        $this->assertEquals(new MemoryDataLayer(), $this->testItem->getApiResourceRetrieverInstance(MemoryDataLayer::class));
    }

    public function testGetApiResourceRetrieverInstance_exception_on_missing()
    {
        $this->expectException(CouldNotConstructApiResourceClassException::class);
        $this->testItem->getApiResourceRetrieverInstance(FileStorageDataLayer::class);
    }

    public function testGetApiResourcePersisterInstance()
    {
        $this->assertFalse($this->testItem->hasApiResourcePersisterInstance(FileStorageDataLayer::class));
        $this->assertTrue($this->testItem->hasApiResourcePersisterInstance(MemoryDataLayer::class));
        $this->assertEquals(new MemoryDataLayer(), $this->testItem->getApiResourcePersisterInstance(MemoryDataLayer::class));
    }

    public function testGetApiResourcePersisterInstance_exception_on_missing()
    {
        $this->expectException(CouldNotConstructApiResourceClassException::class);
        $this->testItem->getApiResourcePersisterInstance(FileStorageDataLayer::class);
    }
}
