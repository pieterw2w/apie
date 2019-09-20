<?php
namespace W2w\Test\Apie\Resources;

use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Resources\ApiResources;
use W2w\Test\Apie\Mocks\Data\SimplePopo;

class ApiResourcesTest extends TestCase
{
    public function testGetter()
    {
        $testItem = new ApiResources([SimplePopo::class]);
        $this->assertEquals([SimplePopo::class], $testItem->getApiResources());
    }
}
