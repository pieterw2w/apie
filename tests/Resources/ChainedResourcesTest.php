<?php
namespace W2w\Test\Apie\Resources;

use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\ApiResources\Status;
use W2w\Lib\Apie\Exceptions\BadConfigurationException;
use W2w\Lib\Apie\Resources\ApiResources;
use W2w\Lib\Apie\Resources\ChainedResources;
use W2w\Test\Apie\Mocks\Data\SimplePopo;
use W2w\Test\Apie\OpenApiSchema\Data\RecursiveObject;

class ChainedResourcesTest extends TestCase
{
    public function testGetters()
    {
        $testItem = new ChainedResources([
            SimplePopo::class,
            new ApiResources([Status::class, RecursiveObject::class, SimplePopo::class])
        ]);
        $this->assertEquals(
            [SimplePopo::class, Status::class, RecursiveObject::class],
            $testItem->getApiResources()
        );
    }

    public function testInvalidConstructorArguments()
    {
        $this->expectException(BadConfigurationException::class);
        new ChainedResources([[SimplePopo::class]]);
    }
}
