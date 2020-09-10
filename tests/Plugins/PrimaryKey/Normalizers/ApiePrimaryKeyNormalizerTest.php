<?php


namespace W2w\Test\Apie\Plugins\PrimaryKey\Normalizers;

use Doctrine\Common\Annotations\AnnotationReader;
use LogicException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Serializer;
use W2w\Lib\Apie\Core\ApiResourceMetadataFactory;
use W2w\Lib\Apie\Core\Bridge\FrameworkLessConnection;
use W2w\Lib\Apie\Core\ClassResourceConverter;
use W2w\Lib\Apie\Core\IdentifierExtractor;
use W2w\Lib\Apie\Core\Resources\ApiResources;
use W2w\Lib\Apie\DefaultApie;
use W2w\Lib\Apie\Plugins\Core\ResourceFactories\FallbackFactory;
use W2w\Lib\Apie\Plugins\PrimaryKey\Normalizers\ApiePrimaryKeyNormalizer;
use W2w\Lib\Apie\Plugins\PrimaryKey\Normalizers\PrimaryKeyReferenceNormalizer;
use W2w\Lib\Apie\Plugins\PrimaryKey\ValueObjects\PrimaryKeyReference;
use W2w\Lib\Apie\Plugins\Uuid\Normalizers\UuidNormalizer;
use W2w\Lib\ApieObjectAccessNormalizer\Normalizers\ApieObjectAccessNormalizer;
use W2w\Lib\ApieObjectAccessNormalizer\ObjectAccess\ObjectAccess;
use W2w\Test\Apie\OpenApiSchema\Data\RecursiveObjectWithId;

class ApiePrimaryKeyNormalizerTest extends TestCase
{
    /**
     * @var Serializer
     */
    private $serializer;

    protected function setUp(): void
    {
        parent::setUp();
        $resources = new ApiResources([RecursiveObjectWithId::class]);
        $propertyAccess = new ObjectAccess();
        $identifierExtractor = new IdentifierExtractor($propertyAccess);
        $metadataFactory = new ApiResourceMetadataFactory(new AnnotationReader(), new FallbackFactory($propertyAccess, $identifierExtractor));
        $converter = new ClassResourceConverter(new CamelCaseToSnakeCaseNameConverter(), $resources, true);
        $this->serializer = new Serializer(
            [
                new UuidNormalizer(),
                new ApiePrimaryKeyNormalizer($resources, $identifierExtractor, $metadataFactory, $converter, new FrameworkLessConnection(DefaultApie::createDefaultApie())),
                new PrimaryKeyReferenceNormalizer(),
                new ApieObjectAccessNormalizer(null, new CamelCaseToSnakeCaseNameConverter()),
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
        $object = new RecursiveObjectWithId();
        $object->setChild(new RecursiveObjectWithId());
        $object->getChild()->setChild(New RecursiveObjectWithId());
        $actual = $this->serializer->normalize($object);
        $this->assertEquals(
            [
                'id' => $object->getId()->toString(),
                'child' => '/recursive_object_with_id/' . $object->getChild()->getId(),
            ],
            $actual
        );
    }
}
