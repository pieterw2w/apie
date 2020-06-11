<?php


namespace W2w\Test\Apie\Plugins\PrimaryKey\Normalizers;


use LogicException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Serializer;
use W2w\Lib\Apie\Annotations\ApiResource;
use W2w\Lib\Apie\Core\Models\ApiResourceClassMetadata;
use W2w\Lib\Apie\Plugins\PrimaryKey\Normalizers\PrimaryKeyReferenceNormalizer;
use W2w\Lib\Apie\Plugins\PrimaryKey\ValueObjects\PrimaryKeyReference;
use W2w\Test\Apie\Mocks\ApiResources\SimplePopo;

class PrimaryKeyReferenceNormalizerTest extends TestCase
{
    /**
     * @var Serializer
     */
    private $serializer;

    protected function setUp(): void
    {
        $this->serializer = new Serializer(
            [
                new PrimaryKeyReferenceNormalizer(),
            ],
            []
        );
    }

    public function testDenormalize()
    {
        $this->expectException(LogicException::class);
        $this->serializer->denormalize('/test/1', PrimaryKeyReference::class);
    }

    public function testNormalize()
    {
        $actual = $this->serializer->normalize(
            new PrimaryKeyReference(
                new ApiResourceClassMetadata(
                    SimplePopo::class,
                    new ApiResource(),
                    null,
                    null
                ),
                'simple_popo',
                '42'
            )
        );
        $this->assertEquals('simple_popo/42', $actual);
    }
}
