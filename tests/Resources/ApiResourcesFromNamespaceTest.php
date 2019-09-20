<?php
namespace W2w\Test\Apie\Resources;

use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\ApiResources\App;
use W2w\Lib\Apie\ApiResources\Status;
use W2w\Lib\Apie\Resources\ApiResourcesFromNamespace;

class ApiResourcesFromNamespaceTest extends TestCase
{
    public function testGetters()
    {
        $testItem = new ApiResourcesFromNamespace('W2w\Lib\Apie\ApiResources');
        $this->assertEquals([App::class, Status::class], $testItem->getApiResources());
    }
}
