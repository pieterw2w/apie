<?php
namespace W2w\Test\Apie\Core;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use W2w\Lib\Apie\Annotations\ApiResource;
use W2w\Lib\Apie\Core\ApiResourceMetadataFactory;
use W2w\Lib\Apie\Core\ApiResourcePersister;
use W2w\Lib\Apie\Core\Models\ApiResourceClassMetadata;
use W2w\Lib\Apie\Exceptions\InvalidReturnTypeOfApiResourceException;
use W2w\Lib\Apie\Exceptions\MethodNotAllowedException;
use W2w\Lib\Apie\Interfaces\ApiResourcePersisterInterface;
use W2w\Test\Apie\Mocks\ApiResources\SimplePopo;
use W2w\Test\Apie\OpenApiSchema\Data\RecursiveObject;

class ApiResourcePersisterTest extends TestCase
{
    private $factory;

    private $testItem;

    protected function setUp(): void
    {
        $this->factory = $this->prophesize(ApiResourceMetadataFactory::class);
        $this->testItem = new ApiResourcePersister($this->factory->reveal());
    }

    public function testPersistNew()
    {
        $object = new SimplePopo();
        $persister = $this->prophesize(ApiResourcePersisterInterface::class);
        $persister->persistNew(Argument::type(SimplePopo::class), [])
            ->shouldBeCalled()
            ->willReturnArgument(0);
        $this->factory->getMetadata(SimplePopo::class)
            ->shouldBeCalled()
            ->willReturn(new ApiResourceClassMetadata(SimplePopo::class, new ApiResource(), null, $persister->reveal()));
        $this->testItem->persistNew($object);
    }

    public function testPersistNew_persister_returns_wrong_object()
    {
        $object = new SimplePopo();
        $persister = $this->prophesize(ApiResourcePersisterInterface::class);
        $persister->persistNew(Argument::type(SimplePopo::class), [])
            ->shouldBeCalled()
            ->willReturn(new RecursiveObject());
        $this->factory->getMetadata(SimplePopo::class)
            ->shouldBeCalled()
            ->willReturn(new ApiResourceClassMetadata(SimplePopo::class, new ApiResource(), null, $persister->reveal()));
        $this->expectException(InvalidReturnTypeOfApiResourceException::class);
        $this->testItem->persistNew($object);
    }

    public function testPersistNew_persister_returns_string()
    {
        $object = new SimplePopo();
        $persister = $this->prophesize(ApiResourcePersisterInterface::class);
        $persister->persistNew(Argument::type(SimplePopo::class), [])
            ->shouldBeCalled()
            ->willReturn('this is a string');
        $this->factory->getMetadata(SimplePopo::class)
            ->shouldBeCalled()
            ->willReturn(new ApiResourceClassMetadata(SimplePopo::class, new ApiResource(), null, $persister->reveal()));
        $this->expectException(InvalidReturnTypeOfApiResourceException::class);
        $this->testItem->persistNew($object);
    }

    public function testPersistNew_persister_returns_null()
    {
        $object = new SimplePopo();
        $persister = $this->prophesize(ApiResourcePersisterInterface::class);
        $persister->persistNew(Argument::type(SimplePopo::class), [])
            ->shouldBeCalled()
            ->willReturn(null);
        $this->factory->getMetadata(SimplePopo::class)
            ->shouldBeCalled()
            ->willReturn(new ApiResourceClassMetadata(SimplePopo::class, new ApiResource(), null, $persister->reveal()));
        $this->expectException(InvalidReturnTypeOfApiResourceException::class);
        $this->testItem->persistNew($object);
    }

    public function testPersistNew_method_is_not_allowed()
    {
        $object = new SimplePopo();
        $this->factory->getMetadata(SimplePopo::class)
                      ->shouldBeCalled()
                      ->willReturn(New ApiResourceClassMetadata(SimplePopo::class, new ApiResource(), null, null));
        $this->expectException(MethodNotAllowedException::class);
        $this->testItem->persistNew($object);

    }
}
