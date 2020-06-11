<?php
namespace W2w\Test\Apie\Core\Resources;

use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Core\Resources\ApiResourcesFromNamespace;
use W2w\Lib\Apie\Plugins\ApplicationInfo\ApiResources\ApplicationInfo;
use W2w\Lib\Apie\Plugins\StatusCheck\ApiResources\Status;
use W2w\Test\Apie\OpenApiSchema\Data\MultipleTypesObject;
use W2w\Test\Apie\OpenApiSchema\Data\RecursiveObject;
use W2w\Test\Apie\OpenApiSchema\Data\RecursiveObjectWithId;

class ApiResourcesFromNamespaceTest extends TestCase
{
    public function testGetters()
    {
        $testItem = new ApiResourcesFromNamespace('W2w\Lib\Apie\Plugins\ApplicationInfo\ApiResources');
        $this->assertEquals([ApplicationInfo::class], $testItem->getApiResources());
    }

    public function testCreateApiResources()
    {
        $this->assertEquals(
            [MultipleTypesObject::class, RecursiveObject::class, RecursiveObjectWithId::class, ApplicationInfo::class, Status::class],
            ApiResourcesFromNamespace::createApiResources('W2w\Test\Apie\OpenApiSchema\Data')
        );
        $this->assertEquals(
            [MultipleTypesObject::class, RecursiveObject::class, RecursiveObjectWithId::class],
            ApiResourcesFromNamespace::createApiResources('W2w\Test\Apie\OpenApiSchema\Data', false)
        );

    }
}
