<?php
namespace W2w\Test\Apie\Plugins\StatusCheck\DataLayers;

use ArrayIterator;
use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Core\SearchFilters\SearchFilterRequest;
use W2w\Lib\Apie\Exceptions\InvalidClassTypeException;
use W2w\Lib\Apie\Exceptions\ResourceNotFoundException;
use W2w\Lib\Apie\Plugins\StatusCheck\ApiResources\Status;
use W2w\Lib\Apie\Plugins\StatusCheck\DataLayers\StatusCheckRetriever;
use W2w\Lib\Apie\Plugins\StatusCheck\StatusChecks\StaticStatusCheck;
use W2w\Lib\Apie\Plugins\StatusCheck\StatusChecks\StatusCheckListInterface;

class StatusCheckRetrieverTest extends TestCase
{
    private $testItem;

    private $listCheck;

    protected function setUp(): void
    {
        $this->listCheck = $this->prophesize(StatusCheckListInterface::class);
        $this->listCheck->getIterator()
            ->willReturn(
                new ArrayIterator(
                    [
                        new StaticStatusCheck(new Status('from list check', 'OK', 'https://php.net', [])),
                        new Status('a status object', 'OK', 'https://php.net', []),
                    ]
                )
            );

        $statusChecks = [
            $this->listCheck->reveal(),
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

    public function testRetrieveAll_wrong_status_check()
    {
        $this->testItem = new StatusCheckRetriever([$this]);
        $actual = $this->testItem->retrieveAll(Status::class, [], new SearchFilterRequest(0, 10));
        $this->expectException(InvalidClassTypeException::class);
        iterator_to_array($actual);
    }

    public function testRetrieveAll_wrong_status_check_in_list()
    {
        $listItem = $this->prophesize(StatusCheckListInterface::class);
        $listItem->getIterator()->willReturn(
            new ArrayIterator([$this])
        );

        $this->testItem = new StatusCheckRetriever([$listItem->reveal()]);
        $actual = $this->testItem->retrieveAll(Status::class, [], new SearchFilterRequest(0, 10));
        $this->expectException(InvalidClassTypeException::class);
        iterator_to_array($actual);
    }

    public function testRetrieveAll()
    {
        $this->assertEquals(
            [
                new Status('from list check', 'OK', 'https://php.net', []),
                new Status('a status object', 'OK', 'https://php.net', []),
                new Status('static test', 'OK', 'https://phpunit.de', []),
            ],
            iterator_to_array($this->testItem->retrieveAll(Status::class, [], new SearchFilterRequest(0, 10)))
        );
    }

    public function testRetrieve_entry_not_found()
    {
        $this->expectException(ResourceNotFoundException::class);
        $this->testItem->retrieve(Status::class, 'not found', []);
    }
}
