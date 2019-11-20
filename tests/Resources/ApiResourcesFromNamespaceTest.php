<?php
namespace W2w\Test\Apie\Resources;

use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\ApiResources\ApplicationInfo;
use W2w\Lib\Apie\ApiResources\Status;
use W2w\Lib\Apie\Resources\ApiResourcesFromNamespace;
use W2w\Test\Apie\OpenApiSchema\Data\MultipleTypesObject;
use W2w\Test\Apie\OpenApiSchema\Data\RecursiveObject;

class ApiResourcesFromNamespaceTest extends TestCase
{
    public function testGetters()
    {
        $testItem = new ApiResourcesFromNamespace('W2w\Lib\Apie\ApiResources');
        $this->assertEquals([ApplicationInfo::class, Status::class], $testItem->getApiResources());
    }

    public function testCreateApiResources()
    {
        $this->assertEquals(
            [MultipleTypesObject::class, RecursiveObject::class, ApplicationInfo::class, Status::class],
            ApiResourcesFromNamespace::createApiResources('W2w\Test\Apie\OpenApiSchema\Data')
        );
        $this->assertEquals(
            [MultipleTypesObject::class, RecursiveObject::class],
            ApiResourcesFromNamespace::createApiResources('W2w\Test\Apie\OpenApiSchema\Data', false)
        );

    }
}
