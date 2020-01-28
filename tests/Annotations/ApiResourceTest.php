<?php


namespace W2w\Test\Apie\Annotations;

use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Annotations\ApiResource;
use W2w\Lib\Apie\Plugins\Core\DataLayers\NullDataLayer;

class ApiResourceTest extends TestCase
{
    public function testCreateFromArray()
    {
        $expected = new ApiResource();
        $expected->persistClass = NullDataLayer::class;

        $this->assertEquals(
            $expected,
            ApiResource::createFromArray(['persistClass' => NullDataLayer::class])
        );
    }
}
