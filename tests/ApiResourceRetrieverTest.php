<?php
namespace W2w\Test\Apie;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use W2w\Lib\Apie\Annotations\ApiResource;
use W2w\Lib\Apie\ApiResourceMetadataFactory;
use W2w\Lib\Apie\ApiResourceRetriever;
use W2w\Lib\Apie\Exceptions\InvalidReturnTypeOfApiResourceException;
use W2w\Lib\Apie\Exceptions\MethodNotAllowedException;
use W2w\Lib\Apie\Models\ApiResourceClassMetadata;
use W2w\Lib\Apie\Persisters\ApiResourcePersisterInterface;
use W2w\Lib\Apie\Retrievers\ApiResourceRetrieverInterface;
use W2w\Test\Apie\Mocks\Data\FullRestObject;
use W2w\Test\Apie\Mocks\Data\SimplePopo;
use W2w\Test\Apie\OpenApiSchema\Data\RecursiveObject;

class ApiResourceRetrieverTest extends TestCase
{
    private $factory;

    private $testItem;

    protected function setUp(): void
    {
        $this->factory = $this->prophesize(ApiResourceMetadataFactory::class);
        $this->testItem = new ApiResourceRetriever($this->factory->reveal());
    }

    public function testRetrieve()
    {
        $retriever = $this->prophesize(ApiResourceRetrieverInterface::class);
        $apiResource = new ApiResource();
        $retriever->retrieve(FullRestObject::class, '986e12c4-3011-4ed8-aead-c62b76bb7f69', [])
            ->shouldBeCalled()
            ->willReturn(
                new FullRestObject(Uuid::fromString('986e12c4-3011-4ed8-aead-c62b76bb7f69'))
            );
        $this->factory->getMetadata(FullRestObject::class)
            ->willReturn(
                new ApiResourceClassMetadata(
                    FullRestObject::class,
                    $apiResource,
                    $retriever->reveal(),
                    null
                )
            );
        $this->assertEquals(
            new FullRestObject(Uuid::fromString('986e12c4-3011-4ed8-aead-c62b76bb7f69')),
            $this->testItem->retrieve(FullRestObject::class, '986e12c4-3011-4ed8-aead-c62b76bb7f69')
        );
    }

    /**
     * @dataProvider retrieveWrongObjectProvider
     */
    public function testRetrieve_retriever_returns_wrong_object($wrongOutput)
    {
        $retriever = $this->prophesize(ApiResourceRetrieverInterface::class);
        $apiResource = new ApiResource();
        $retriever->retrieve(FullRestObject::class, '986e12c4-3011-4ed8-aead-c62b76bb7f69', [])
            ->shouldBeCalled()
            ->willReturn(
                $wrongOutput
            );
        $this->factory->getMetadata(FullRestObject::class)
            ->willReturn(
                new ApiResourceClassMetadata(
                FullRestObject::class,
                $apiResource,
                $retriever->reveal(),
                null
            )
        );
        $this->expectException(InvalidReturnTypeOfApiResourceException::class);
        $this->testItem->retrieve(FullRestObject::class, '986e12c4-3011-4ed8-aead-c62b76bb7f69');
    }

    public function retrieveWrongObjectProvider()
    {
        yield [$this];
        yield ['string'];
        yield [42];
    }

    public function testRetrieve_method_not_allowed()
    {
        $apiResource = new ApiResource();
        $this->factory->getMetadata(FullRestObject::class)
           ->willReturn(
               new ApiResourceClassMetadata(
                   FullRestObject::class,
                   $apiResource,
                   null,
                   null
               )
           );
        $this->expectException(MethodNotAllowedException::class);
        $this->testItem->retrieve(FullRestObject::class, '986e12c4-3011-4ed8-aead-c62b76bb7f69');
    }

    public function testRetrieveAll()
    {
        $retriever = $this->prophesize(ApiResourceRetrieverInterface::class);
        $apiResource = new ApiResource();
        $retriever->retrieveAll(FullRestObject::class, [], 0, 10)
            ->shouldBeCalled()
            ->willReturn(
                [new FullRestObject(Uuid::fromString('986e12c4-3011-4ed8-aead-c62b76bb7f69'))]
            );
        $this->factory->getMetadata(FullRestObject::class)
            ->willReturn(
                new ApiResourceClassMetadata(
                    FullRestObject::class,
                    $apiResource,
                    $retriever->reveal(),
                    null
                )
            );
        $this->assertEquals(
            [new FullRestObject(Uuid::fromString('986e12c4-3011-4ed8-aead-c62b76bb7f69'))],
            $this->testItem->retrieveAll(FullRestObject::class, 0, 10)
        );
    }

    public function testRetrieveAll_method_not_allowed()
    {
        $apiResource = new ApiResource();
        $apiResource->disabledMethods = ['get'];
        $this->factory->getMetadata(FullRestObject::class)
            ->willReturn(
                new ApiResourceClassMetadata(
                FullRestObject::class,
                $apiResource,
                null,
                null
            )
        );
        $this->expectException(MethodNotAllowedException::class);
        $this->testItem->retrieveAll(FullRestObject::class, 0, 10);
    }

    public function testRetrieveAll_empty_array_fallback()
    {
        $apiResource = new ApiResource();
        $this->factory->getMetadata(FullRestObject::class)
            ->willReturn(
                new ApiResourceClassMetadata(
                FullRestObject::class,
                $apiResource,
                null,
                null
            )
        );
        $this->assertEquals(
            [],
            $this->testItem->retrieveAll(FullRestObject::class, 0, 10)
        );
    }

    /**
     * @dataProvider retrieveWrongObjectsProvider
     */
    public function testRetrieveAll_retriever_returns_wrong_object($wrongOutput)
    {
        $retriever = $this->prophesize(ApiResourceRetrieverInterface::class);
        $apiResource = new ApiResource();
        $retriever->retrieveAll(FullRestObject::class, [], 0, 10)
            ->shouldBeCalled()
            ->willReturn($wrongOutput);
        $this->factory->getMetadata(FullRestObject::class)
            ->willReturn(
                new ApiResourceClassMetadata(
                FullRestObject::class,
                $apiResource,
                $retriever->reveal(),
                null
                )
            );
        $this->expectException(InvalidReturnTypeOfApiResourceException::class);
        $this->testItem->retrieveAll(FullRestObject::class, 0, 10);
    }

    public function retrieveWrongObjectsProvider()
    {
        yield [[new FullRestObject(), $this]];
        yield [['string']];
        yield [[42]];
    }
}
