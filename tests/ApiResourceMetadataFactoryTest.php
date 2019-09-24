<?php
namespace W2w\Test\Apie;

use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Annotations\ApiResource;
use W2w\Lib\Apie\ApiResourceFactoryInterface;
use W2w\Lib\Apie\ApiResourceMetadataFactory;
use W2w\Lib\Apie\Exceptions\ApiResourceAnnotationNotFoundException;
use W2w\Lib\Apie\Models\ApiResourceClassMetadata;
use W2w\Lib\Apie\Persisters\NullPersister;
use W2w\Test\Apie\Mocks\Data\SumExample;

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
        $this->retrieverFactory->getApiResourcePersisterInstance(NullPersister::class)->willReturn(new NullPersister());
        $apiResource = new ApiResource();
        $apiResource->disabledMethods = ['get'];
        $apiResource->persistClass = NullPersister::class;
        $this->assertEquals(
            new ApiResourceClassMetadata(SumExample::class, $apiResource, null, new NullPersister()),
            $this->testItem->getMetadata(SumExample::class)
        );
    }

    public function testGetMetadata_no_api_resource()
    {
        $this->expectException(ApiResourceAnnotationNotFoundException::class);
        $this->testItem->getMetadata(__CLASS__);
    }
}
