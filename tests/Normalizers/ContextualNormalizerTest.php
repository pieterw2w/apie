<?php


namespace W2w\Test\Apie\Normalizers;

use DateTime;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Serializer;
use W2w\Lib\Apie\Normalizers\ContextualNormalizer;
use W2w\Test\Apie\Mocks\Data\SimplePopo;

class ContextualNormalizerTest extends TestCase
{
    private $normalizer;

    private $testItem;

    private $serializer;

    protected function setUp(): void
    {
        $this->normalizer = new SimplePopoNormalizer();
        $this->testItem = new ContextualNormalizer([$this->normalizer]);
        $this->serializer = new Serializer([$this->testItem], [new JsonEncode()]);
        $this->hackCleanContextualNormalizer();
    }

    protected function tearDown(): void
    {
        $this->hackCleanContextualNormalizer();
    }

    public function testNormalize()
    {
        $input = new SimplePopo();
        $expected = [
            'id' => $input->getId(),
            'created_at' => $input->getCreatedAt()
        ];
        $this->assertEquals($expected, $this->serializer->normalize($input));
        ContextualNormalizer::disableDenormalizer(SimplePopoNormalizer::class);
        $this->assertEquals($expected, $this->serializer->normalize($input));
        ContextualNormalizer::disableNormalizer(SimplePopoNormalizer::class);
        $this->expectException(NotNormalizableValueException::class);
        $this->serializer->normalize($input);
    }

    public function testDenormalize()
    {
        $input = [
            'id' => '123',
            'created_at' => new DateTime()
        ];
        $actual = $this->serializer->denormalize($input, SimplePopo::class);
        $this->assertTrue($actual instanceof SimplePopo);
        $this->assertEquals('123', $actual->getId());
        $this->assertEquals($input['created_at'], $actual->getCreatedAt());
        ContextualNormalizer::disableNormalizer(SimplePopoNormalizer::class);
        $actual = $this->serializer->denormalize($input, SimplePopo::class);
        $this->assertTrue($actual instanceof SimplePopo);
        $this->assertEquals('123', $actual->getId());
        $this->assertEquals($input['created_at'], $actual->getCreatedAt());
        ContextualNormalizer::disableDenormalizer(SimplePopoNormalizer::class);

        $this->expectException(NotNormalizableValueException::class);
        $this->serializer->normalize($input);
    }

    private function hackCleanContextualNormalizer()
    {
        $reflClass = new ReflectionClass(ContextualNormalizer::class);
        $prop = $reflClass->getProperty('globalDisabledNormalizers');
        $prop->setAccessible(true);
        $prop->setValue([]);

        $prop = $reflClass->getProperty('globalDisabledDenormalizers');
        $prop->setAccessible(true);
        $prop->setValue([]);
    }
}
