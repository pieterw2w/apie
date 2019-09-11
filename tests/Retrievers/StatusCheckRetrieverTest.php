<?php
namespace W2w\Test\Apie\Retrievers;

use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\ApiResources\Status;
use W2w\Lib\Apie\Exceptions\ResourceNotFoundException;
use W2w\Lib\Apie\Retrievers\StatusCheckRetriever;
use W2w\Lib\Apie\StatusChecks\StaticStatusCheck;

class StatusCheckRetrieverTest extends TestCase
{
    private $testItem;

    protected function setUp(): void
    {
        $statusChecks = [
            new StaticStatusCheck(new Status('static test', 'OK', 'https://phpunit.de', []))
        ];
        $this->testItem = new StatusCheckRetriever($statusChecks);
    }

    public function testRetrieve()
    {
        $this->assertEquals(
            new Status('static test', 'OK', 'https://phpunit.de', []),
            $this->testItem->retrieve(Status::class, 'static test', [])
        );
    }

    public function testRetrieveAll()
    {
        $this->assertEquals(
            [new Status('static test', 'OK', 'https://phpunit.de', [])],
            iterator_to_array($this->testItem->retrieveAll(Status::class, [], 0, 10))
        );
    }

    public function testRetrieveNotFound()
    {
        $this->expectException(ResourceNotFoundException::class);
        $this->testItem->retrieve(Status::class, 'not found', []);
    }
}
