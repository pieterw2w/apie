<?php
namespace W2w\Test\Apie\Mocks;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\PropertyAccess\PropertyAccess;
use W2w\Lib\Apie\Exceptions\ResourceNotFoundException;
use W2w\Lib\Apie\IdentifierExtractor;
use W2w\Lib\Apie\Mocks\MockApiResourceDataLayer;
use W2w\Lib\Apie\SearchFilters\SearchFilterRequest;
use W2w\Test\Apie\Mocks\Data\SimplePopo;
use W2w\Test\Apie\Mocks\Data\SumExample;

class MockApiResourceDataLayerTest extends TestCase
{
    private $cache;

    private $testItem;

    protected function setUp(): void
    {
        $this->cache = new ArrayAdapter();
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $this->testItem = new MockApiResourceDataLayer(
            $this->cache,
            new IdentifierExtractor($propertyAccessor),
            $propertyAccessor
        );
    }

    public function testPersistNew_no_id_ignore_persist()
    {
        $resource = new SumExample(1, 2);
        $this->testItem->persistNew($resource, []);
        $cacheItem = $this->cache->getItem('mock-server-all.SumExample');
        $this->assertFalse($cacheItem->isHit());
    }

    public function testPersistNew()
    {
        $request = new SearchFilterRequest(0, 100);
        $resource1 = new SimplePopo();
        $resource2 = new SimplePopo();
        $this->assertEquals([], $this->testItem->retrieveAll(SimplePopo::class, [], $request));

        $this->testItem->persistNew($resource1, []);

        $this->assertEquals([$resource1], $this->testItem->retrieveAll(SimplePopo::class, [], $request));

        $this->testItem->persistNew($resource2, []);
        $this->assertEquals([$resource1, $resource2], $this->testItem->retrieveAll(SimplePopo::class, [], $request));

        $resource1->arbitraryField = 'test';
        $this->assertNotEquals($resource1, $this->testItem->retrieve(SimplePopo::class, $resource1->getId(), []));

        $this->testItem->persistExisting($resource1, $resource1->getId(), []);
        $this->assertEquals($resource1, $this->testItem->retrieve(SimplePopo::class, $resource1->getId(), []));

        $this->testItem->remove(SimplePopo::class, $resource1->getId(), []);
        $this->assertEquals([$resource2], $this->testItem->retrieveAll(SimplePopo::class, [], $request));
    }

    public function testRetrieveThrowsError()
    {
        $this->expectException(ResourceNotFoundException::class);
        $this->testItem->retrieve(SimplePopo::class, 1, []);
    }
}
