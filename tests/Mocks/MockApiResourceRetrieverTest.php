<?php
namespace W2w\Test\Apie\Mocks;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\PropertyAccess\PropertyAccess;
use W2w\Lib\Apie\Mocks\MockApiResourceRetriever;
use W2w\Test\Apie\Mocks\Data\SimplePopo;

class MockApiResourceRetrieverTest extends TestCase
{
    private $cache;

    private $testItem;

    protected function setUp(): void
    {
        $this->cache = new ArrayAdapter();
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $this->testItem = new MockApiResourceRetriever($this->cache, $propertyAccessor);
    }

    public function testPersistNew()
    {
        $resource1 = new SimplePopo();
        $resource2 = new SimplePopo();
        $this->assertEquals([], $this->testItem->retrieveAll(SimplePopo::class, [], 0, 100));

        $this->testItem->persistNew($resource1, []);

        $this->assertEquals([$resource1], $this->testItem->retrieveAll(SimplePopo::class, [], 0, 100));

        $this->testItem->persistNew($resource2, []);
        $this->assertEquals([$resource1, $resource2], $this->testItem->retrieveAll(SimplePopo::class, [], 0, 100));

        $resource1->arbitraryField = 'test';
        $this->assertNotEquals($resource1, $this->testItem->retrieve(SimplePopo::class, $resource1->getId(), []));

        $this->testItem->persistExisting($resource1, $resource1->getId(), []);
        $this->assertEquals($resource1, $this->testItem->retrieve(SimplePopo::class, $resource1->getId(), []));

        $this->testItem->remove(SimplePopo::class, $resource1->getId(), []);
        $this->assertEquals([$resource2], $this->testItem->retrieveAll(SimplePopo::class, [], 0, 100));
    }
}
