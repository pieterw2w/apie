<?php
namespace W2w\Test\Apie\Core\Resources;

use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Core\Resources\ApiResources;
use W2w\Test\Apie\Mocks\ApiResources\SimplePopo;

class ApiResourcesTest extends TestCase
{
    public function testGetter()
    {
        $testItem = new ApiResources([SimplePopo::class]);
        $this->assertEquals([SimplePopo::class], $testItem->getApiResources());
    }
}
