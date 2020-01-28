<?php
namespace W2w\Test\Apie\Plugins\Core\DataLayers;

use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Plugins\Core\DataLayers\NullDataLayer;
use W2w\Test\Apie\Mocks\ApiResources\SimplePopo;

class NullDataLayerTest extends TestCase
{
    public function testNothing()
    {
        $testItem = new NullDataLayer();
        $resource = $this->prophesize(SimplePopo::class);
        // added so any method call will throw an error.
        $resource->getId()->shouldNotBeCalled();
        $testItem->persistNew($resource->reveal(), []);
        $testItem->persistExisting($resource->reveal(), []);
        $testItem->remove(SimplePopo::class, 1, []);
    }
}
