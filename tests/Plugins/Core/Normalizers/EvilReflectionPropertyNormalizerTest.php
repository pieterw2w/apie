<?php
namespace W2w\Test\Apie\Plugins\Core\Normalizers;

use DateTime;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Serializer;
use W2w\Lib\Apie\Plugins\Core\Normalizers\EvilReflectionPropertyNormalizer;
use W2w\Test\Apie\Mocks\ApiResources\SimplePopo;

class EvilReflectionPropertyNormalizerTest extends TestCase
{
    private $testItem;

    private $serializer;

    protected function setUp(): void
    {
        $this->testItem = new EvilReflectionPropertyNormalizer(null, null, null, null, null, null, []);
        $this->serializer = new Serializer([$this->testItem], [new JsonEncode()]);
    }

    public function testEvilReflection()
    {
        $createdAt = new DateTime();
        $actual = $this->serializer->denormalize(['id' => '123', 'createdAt' => $createdAt], SimplePopo::class);
        $this->assertEquals('123', $actual->getId());
        $this->assertEquals($createdAt, $actual->getCreatedAt());
    }

    public function testIgnoreUnknownProperty()
    {
        $createdAt = new DateTime();
        $actual = $this->serializer->denormalize(['id' => '123', 'createdAt' => $createdAt, 'ignored' => true], SimplePopo::class);
        $this->assertEquals('123', $actual->getId());
        $this->assertEquals($createdAt, $actual->getCreatedAt());
    }
}
