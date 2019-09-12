<?php
namespace W2w\Test\Apie\Persisters;

use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Persisters\NullPersister;
use W2w\Test\Apie\Mocks\Data\SimplePopo;

class NullPersisterTest extends TestCase
{
    public function testNothing()
    {
        $testItem = new NullPersister();
        $resource = $this->prophesize(SimplePopo::class);
        // added so any method call will throw an error.
        $resource->getId()->shouldNotBeCalled();
        $testItem->persistNew($resource->reveal(), []);
        $testItem->persistExisting($resource->reveal(), []);
        $testItem->remove(SimplePopo::class, 1, []);
    }
}
