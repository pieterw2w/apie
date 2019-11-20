<?php
namespace W2w\Test\Apie\Models;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use W2w\Lib\Apie\Encodings\FormatRetriever;
use W2w\Lib\Apie\Models\ApiResourceFacadeResponse;
use W2w\Test\Apie\Mocks\Data\SimplePopo;

class ApiResourceFacadeResponseTest extends TestCase
{
    public function testGetters()
    {
        $serializer = $this->prophesize(SerializerInterface::class)
            ->willImplement(NormalizerInterface::class);

        $resource = new SimplePopo();

        $formatRetriever = new FormatRetriever();

        $testItem = new ApiResourceFacadeResponse(
            $serializer->reveal(),
            [],
            $resource,
            $formatRetriever,
            'application/xhtml+xml'
        );

        $this->assertEquals($resource, $testItem->getResource());

        $data = ['id' => 123, 'created-at' => 'today'];
        $serializer->normalize($resource, 'xml', [])
                   ->shouldBeCalled()
                   ->willReturn($data);

        $this->assertEquals($data, $testItem->getNormalizedData());

        $xml = '<response><id>123</id><created-at>today</created-at></response>';
        $serializer->serialize($resource, 'xml', [])
                   ->shouldBeCalled()
                   ->willReturn($xml);

        $actual = $testItem->getResponse();
        $this->assertEquals(200, $actual->getStatusCode());
        $this->assertEquals($xml, (string) $actual->getBody());
        $this->assertEquals('application/xml', $actual->getHeader('content-type')[0] ?? null);
    }
}
