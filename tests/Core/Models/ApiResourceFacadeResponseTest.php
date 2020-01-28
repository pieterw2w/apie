<?php
namespace W2w\Test\Apie\Core\Models;

use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Core\Models\ApiResourceFacadeResponse;
use W2w\Lib\Apie\Interfaces\ResourceSerializerInterface;
use W2w\Test\Apie\Mocks\ApiResources\SimplePopo;
use Zend\Diactoros\Response\TextResponse;

class ApiResourceFacadeResponseTest extends TestCase
{
    public function testGetters()
    {
        $serializer = $this->prophesize(ResourceSerializerInterface::class);

        $resource = new SimplePopo();

        $testItem = new ApiResourceFacadeResponse(
            $serializer->reveal(),
            $resource,
            'application/xhtml+xml'
        );

        $this->assertEquals($resource, $testItem->getResource());

        $data = ['id' => 123, 'created-at' => 'today'];
        $serializer->normalize($resource, 'application/xhtml+xml')
            ->shouldBeCalled()
            ->willReturn($data);

        $this->assertEquals($data, $testItem->getNormalizedData());

        $xml = '<response><id>123</id><created-at>today</created-at></response>';
        $response = new TextResponse($xml);
        $serializer->toResponse($resource, 'application/xhtml+xml')
            ->shouldBeCalled()
            ->willReturn($response);

        $actual = $testItem->getResponse();
        $this->assertEquals($response, $actual);
    }
}
