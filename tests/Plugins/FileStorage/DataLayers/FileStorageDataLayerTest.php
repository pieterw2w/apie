<?php
namespace W2w\Test\Apie\Plugins\FileStorage\DataLayers;

use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;
use W2w\Lib\Apie\Core\IdentifierExtractor;
use W2w\Lib\Apie\Core\SearchFilters\SearchFilterRequest;
use W2w\Lib\Apie\Exceptions\InvalidIdException;
use W2w\Lib\Apie\Plugins\FileStorage\DataLayers\FileStorageDataLayer;
use W2w\Lib\ApieObjectAccessNormalizer\ObjectAccess\ObjectAccess;
use W2w\Test\Apie\Mocks\ApiResources\FullRestObject;
use W2w\Test\Apie\Mocks\ApiResources\SimplePopo;

class FileStorageDataLayerTest extends TestCase
{
    private $folder;

    private $testItem;

    protected function setUp(): void
    {
        srand(0);
        $this->folder = sys_get_temp_dir() . DIRECTORY_SEPARATOR . bin2hex(random_bytes(12));
        $identifierExtractor = new IdentifierExtractor(new ObjectAccess());
        $this->testItem = new FileStorageDataLayer($this->folder, $identifierExtractor);
    }

    protected function tearDown(): void
    {
        if ($this->folder && $this->folder !== DIRECTORY_SEPARATOR) {
            system('rm -rf ' . escapeshellarg($this->folder));
        }
    }

    public function testPersistNew()
    {
        $request = new SearchFilterRequest(0, 100);
        $resource1 = new SimplePopo();
        $resource2 = new SimplePopo();
        $this->assertEquals([], $this->testItem->retrieveAll(SimplePopo::class, [], $request)->getCurrentPageResults());

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

    public function testPersistNew_fails_with_unsafe_id()
    {
        $resource = new FullRestObject();
        $resource->stringValue = '../unsafe-id.php';
        $this->expectException(InvalidIdException::class);
        $this->testItem->persistNew($resource, ['identifier' => 'stringValue']);
    }
}
