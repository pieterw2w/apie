<?php
namespace W2w\Test\Apie\Plugins\Core\DataLayers;

use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Core\SearchFilters\SearchFilterRequest;
use W2w\Lib\Apie\Exceptions\CanNotDetermineIdException;
use W2w\Lib\Apie\Exceptions\ResourceNotFoundException;
use W2w\Lib\Apie\Plugins\Core\DataLayers\MemoryDataLayer;
use W2w\Test\Apie\Mocks\ApiResources\SimplePopo;

class MemoryDataLayerTest extends TestCase
{
    private $testItem;

    protected function setUp(): void
    {
        srand(0);
        $this->testItem = new MemoryDataLayer();
    }

    public function testPersistNew()
    {
        $request = new SearchFilterRequest(0, 100);
        $resource1 = new SimplePopo();
        $resource2 = new SimplePopo();
        $this->assertEquals([], $this->testItem->retrieveAll(SimplePopo::class, [], $request));

        $this->testItem->persistNew($resource1, []);

        $this->assertEquals([$resource1], $this->testItem->retrieveAll(SimplePopo::class, [], $request)->getCurrentPageResults());

        $this->testItem->persistNew($resource2, []);
        $this->assertEquals([$resource1, $resource2], $this->testItem->retrieveAll(SimplePopo::class, [], $request)->getCurrentPageResults());

        $resource1->arbitraryField = 'test';
        $this->assertNotEquals($resource1, $this->testItem->retrieve(SimplePopo::class, $resource1->getId(), []));

        $this->testItem->persistExisting($resource1, $resource1->getId(), []);
        $this->assertEquals($resource1, $this->testItem->retrieve(SimplePopo::class, $resource1->getId(), []));

        $this->testItem->remove(SimplePopo::class, $resource1->getId(), []);
        $this->assertEquals([$resource2], $this->testItem->retrieveAll(SimplePopo::class, [], $request)->getCurrentPageResults());
    }

    public function testPersistNew_can_not_determine_identifier()
    {
        $class = new class {
        };
        $this->expectException(CanNotDetermineIdException::class);
        $this->testItem->persistNew($class, []);
    }

    public function testRetrieve_not_found()
    {
        $this->expectException(ResourceNotFoundException::class);
        $this->testItem->retrieve(SimplePopo::class, 123, []);
    }
}
