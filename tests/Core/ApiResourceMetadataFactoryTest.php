<?php
namespace W2w\Test\Apie\Core;

use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Annotations\ApiResource;
use W2w\Lib\Apie\Core\ApiResourceMetadataFactory;
use W2w\Lib\Apie\Core\Models\ApiResourceClassMetadata;
use W2w\Lib\Apie\Exceptions\ApiResourceAnnotationNotFoundException;
use W2w\Lib\Apie\Interfaces\ApiResourceFactoryInterface;
use W2w\Lib\Apie\Plugins\Core\DataLayers\NullDataLayer;
use W2w\Test\Apie\Mocks\ApiResources\SumExample;

class ApiResourceMetadataFactoryTest extends TestCase
{
    private $testItem;

    private $retrieverFactory;

    protected function setUp(): void
    {
        $reader = new AnnotationReader();
        $this->retrieverFactory = $this->prophesize(ApiResourceFactoryInterface::class);
        $this->testItem = new ApiResourceMetadataFactory($reader, $this->retrieverFactory->reveal());
    }

    public function testGetMetadata()
    {
        $this->retrieverFactory->getApiResourcePersisterInstance(NullDataLayer::class)->willReturn(new NullDataLayer());
        $apiResource = new ApiResource();
        $apiResource->disabledMethods = ['get'];
        $apiResource->persistClass = NullDataLayer::class;
        $this->assertEquals(
            new ApiResourceClassMetadata(SumExample::class, $apiResource, null, new NullDataLayer()),
            $this->testItem->getMetadata(SumExample::class)
        );
    }

    public function testGetMetadata_no_api_resource()
    {
        $this->expectException(ApiResourceAnnotationNotFoundException::class);
        $this->testItem->getMetadata(__CLASS__);
    }
}
