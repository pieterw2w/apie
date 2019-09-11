<?php
namespace W2w\Test\Apie\Retrievers;

use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\ApiResources\App;
use W2w\Lib\Apie\Exceptions\ResourceNotFoundException;
use W2w\Lib\Apie\Retrievers\AppRetriever;

class AppRetrieverTest extends TestCase
{
    public function testRetrieve()
    {
        $testItem = new AppRetriever('Unit test app', 'test', 'hash-123', true);
        $this->assertEquals(
            new App('Unit test app', 'test', 'hash-123', true),
            $testItem->retrieve(App::class, 'name', [])
        );
    }

    public function testRetrieveAll()
    {
        $testItem = new AppRetriever('Unit test app', 'test', 'hash-123', true);
        $this->assertEquals(
            [new App('Unit test app', 'test', 'hash-123', true)],
            $testItem->retrieveAll(App::class, [], 0, 10)
        );
        $this->assertEquals(
            [],
            $testItem->retrieveAll(App::class, [], 1, 10)
        );
    }

    public function testRetrieveWrongIdentifier()
    {
        $testItem = new AppRetriever('Unit test app', 'test', 'hash-123', true);
        $this->expectException(ResourceNotFoundException::class);
        $testItem->retrieve(App::class, 'not a name', []);
    }
}
