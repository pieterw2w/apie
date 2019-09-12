<?php
namespace W2w\Test\Apie\Retrievers;

use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;
use W2w\Lib\Apie\Retrievers\FileStorageRetriever;
use W2w\Test\Apie\Mocks\Data\SimplePopo;

class FileStorageRetrieverTest extends TestCase
{
    private $folder;

    private $testItem;

    protected function setUp(): void
    {
        $this->folder = sys_get_temp_dir() . DIRECTORY_SEPARATOR . bin2hex(random_bytes(12));
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $this->testItem = new FileStorageRetriever($this->folder, $propertyAccessor);
    }

    protected function tearDown(): void
    {
        if ($this->folder && $this->folder !== DIRECTORY_SEPARATOR) {
            system('rm -rf ' . escapeshellarg($this->folder));
        }
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
