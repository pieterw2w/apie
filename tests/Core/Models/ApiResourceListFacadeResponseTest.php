<?php


namespace W2w\Test\Apie\Core\Models;

use Laminas\Diactoros\Response\TextResponse;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use W2w\Lib\Apie\Core\Models\ApiResourceListFacadeResponse;
use W2w\Lib\Apie\Core\SearchFilters\SearchFilterRequest;
use W2w\Lib\Apie\Interfaces\ResourceSerializerInterface;
use W2w\Test\Apie\Mocks\ApiResources\SumExample;

class ApiResourceListFacadeResponseTest extends TestCase
{

    public function testGetters()
    {
        $serializer = $this->prophesize(ResourceSerializerInterface::class);
        $dummyObject = new SumExample(5, 2);
        $testItem = new ApiResourceListFacadeResponse(
            $serializer->reveal(),
            [$dummyObject],
            SumExample::class,
            new SearchFilterRequest(),
            'application/json',
            []
        );
        $response = new TextResponse('[{"one":5,"two":2}]', 200, ['Content-Type' => 'application/json']);

        $serializer->toResponse(
            [$dummyObject],
            'application/json'
        )
            ->shouldBeCalled()
            ->willReturn($response);
        $serializer->normalize(
            [$dummyObject],
            'application/json'
        )
            ->shouldBeCalled()
            ->willReturn([['one' => 5, 'two' => 2]]);

        $this->assertEquals([$dummyObject], $testItem->getResource());
        $this->assertEquals('application/json', $testItem->getAcceptHeader());
        $actual = $testItem->getResponse();
        $this->assertSame($response, $actual);

        $actual = $testItem->getNormalizedData();
        $this->assertEquals([['one' => 5, 'two' => 2]], $actual);
    }
}
