<?php


namespace W2w\Test\Apie\Models;

use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Annotations\ApiResource;
use W2w\Lib\Apie\Exceptions\InvalidReturnTypeOfApiResourceException;
use W2w\Lib\Apie\Models\ApiResourceClassMetadata;
use W2w\Lib\Apie\Persisters\ApiResourcePersisterInterface;
use W2w\Lib\Apie\Retrievers\ApiResourceRetrieverInterface;
use W2w\Test\Apie\Mocks\Data\SimplePopo;

class ApiResourceClassMetadataTest extends TestCase
{
    public function testGetResourceRetriever_throws_error_if_missing()
    {
        $testItem = new ApiResourceClassMetadata(SimplePopo::class, new ApiResource(), null, null);
        $this->expectException(InvalidReturnTypeOfApiResourceException::class);
        $testItem->getResourceRetriever();
    }

    public function testGetResourcePersister_throws_error_if_missing()
    {
        $testItem = new ApiResourceClassMetadata(SimplePopo::class, new ApiResource(), null, null);
        $this->expectException(InvalidReturnTypeOfApiResourceException::class);
        $testItem->getResourcePersister();
    }

    public function testGetters()
    {
        $retriever = $this->prophesize(ApiResourceRetrieverInterface::class)->reveal();
        $persister = $this->prophesize(ApiResourcePersisterInterface::class)->reveal();

        $testItem = new ApiResourceClassMetadata(
            SimplePopo::class,
            new ApiResource(),
            $retriever,
            $persister
        );
        $this->assertTrue($testItem->hasResourceRetriever());
        $this->assertEquals($retriever, $testItem->getResourceRetriever());
        $this->assertTrue($testItem->hasResourcePersister());
        $this->assertEquals($persister, $testItem->getResourcePersister());
        $this->assertEquals([], $testItem->getContext());
    }

    /**
     * @dataProvider allowProvider
     */
    public function testAllow(
        bool $expectGetAll,
        bool $expectGet,
        bool $expectPost,
        bool $expectPut,
        bool $expectDelete,
        array $disabledMethods,
        bool $hasRetriever,
        bool $hasPersister
    ) {
        $retriever = $persister = null;
        if ($hasRetriever) {
            $retriever = $this->prophesize(ApiResourceRetrieverInterface::class)->reveal();
        }
        if ($hasPersister) {
            $persister = $this->prophesize(ApiResourcePersisterInterface::class)->reveal();
        }
        $annotation = new ApiResource();
        $annotation->disabledMethods = $disabledMethods;

        $testItem = new ApiResourceClassMetadata(
            SimplePopo::class,
            $annotation,
            $retriever,
            $persister
        );
        $this->assertEquals($expectGetAll, $testItem->allowGetAll(), 'allowGetAll is ' . json_encode($expectGetAll));
        $this->assertEquals($expectGet, $testItem->allowGet(), 'allowGet is ' . json_encode($expectGet));
        $this->assertEquals($expectPost, $testItem->allowPost(), 'allowPost is ' . json_encode($expectPost));
        $this->assertEquals($expectPut, $testItem->allowPut(), 'allowPut is ' . json_encode($expectPut));
        $this->assertEquals($expectDelete, $testItem->allowDelete(), 'allowDelete is ' . json_encode($expectDelete));
    }

    public function allowProvider()
    {
        yield [true, false, false, false, false, [], false, false];
        yield [false, false, false, false, false, ['get'], false, false];
        yield [true, true, false, false, false, [], true, false];
        yield [true, true, true, true, true, [], true, true];
        yield [true, false, true, false, false, [], false, true];
        yield [false, false, true, false, true, ['get'], true, true];
        yield [true, true, false, true, true, ['post'], true, true];
        yield [true, true, true, false, true, ['put'], true, true];
        yield [true, true, true, true, false, ['delete'], true, true];
        yield [true, true, false, true, false, ['post', 'delete'], true, true];
    }
}
