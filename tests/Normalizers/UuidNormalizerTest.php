<?php
namespace W2w\Test\Apie\Normalizers;

use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Serializer;
use W2w\Lib\Apie\Normalizers\UuidNormalizer;

class UuidNormalizerTest extends TestCase
{
    private $testItem;

    private $serializer;

    protected function setUp(): void
    {
        $this->testItem = new UuidNormalizer();
        $this->serializer = new Serializer([$this->testItem], [new JsonEncode(), new JsonDecode()]);
    }

    public function testNormalize()
    {
        $actual = $this->serializer->serialize(Uuid::fromString('986e12c4-3011-4ed8-aead-c62b76bb7f69'), 'json');
        $this->assertEquals('"986e12c4-3011-4ed8-aead-c62b76bb7f69"', $actual);
    }

    public function testDenormalize_with_class_definition()
    {
        $actual = $this->serializer->deserialize('"986e12c4-3011-4ed8-aead-c62b76bb7f69"', Uuid::class, 'json');
        $this->assertEquals(Uuid::fromString('986e12c4-3011-4ed8-aead-c62b76bb7f69'), $actual);
    }

    public function testDenormalize_with_interface_definition()
    {
        $actual = $this->serializer->deserialize('"986e12c4-3011-4ed8-aead-c62b76bb7f69"', UuidInterface::class, 'json');
        $this->assertEquals(Uuid::fromString('986e12c4-3011-4ed8-aead-c62b76bb7f69'), $actual);
    }
}
