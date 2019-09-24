<?php

namespace W2w\Test\Apie\Normalizers;

use DateTime;
use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\Extractor\SerializerExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Mapping\Loader\LoaderChain;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Serializer;
use W2w\Lib\Apie\BaseGroupLoader;
use W2w\Lib\Apie\Normalizers\ApieObjectNormalizer;
use W2w\Test\Apie\Mocks\Data\SimplePopo;
use W2w\Test\Apie\Mocks\Data\SumExample;

class ApieObjectNormalizerTest extends TestCase
{
    public function testWorksTheSameAsObjectNormalizer()
    {
        $normalizer = new ApieObjectNormalizer();
        $serializer = new Serializer([new DateTimeNormalizer(['datetime_format' => DateTime::ATOM]), $normalizer], [new JsonEncoder()]);
        $object = new SimplePopo();
        $actual = $serializer->serialize($object, 'json');
        $this->assertEquals(
            [
                'id' => $object->getId(),
                'createdAt' => $object->getCreatedAt()->format(DateTime::ATOM),
                'arbitraryField' => null,
            ],
            json_decode($actual, true)
        );
    }

    public function testGithub_issue_1()
    {
        $factory = new ClassMetadataFactory(
            new LoaderChain(
                [
                    new AnnotationLoader(new AnnotationReader()),
                    new BaseGroupLoader(['base', 'get', 'set'])
                ]
            )
        );
        $reflectionExtractor = new ReflectionExtractor();
        $phpDocExtractor = new PhpDocExtractor();
        $normalizer = new ApieObjectNormalizer(
            $factory,
            new CamelCaseToSnakeCaseNameConverter(),
            PropertyAccess::createPropertyAccessor(),
            new PropertyInfoExtractor(
                [
                    new SerializerExtractor($factory),
                    $reflectionExtractor,
                ],
                [
                    $phpDocExtractor,
                    $reflectionExtractor,
                ],
                [
                    $phpDocExtractor,
                ],
                [
                    $reflectionExtractor,
                ],
                [
                    $reflectionExtractor,
                ]
            )
        );
        $serializer = new Serializer([new DateTimeNormalizer(['datetime_format' => DateTime::ATOM]), $normalizer], [new JsonEncoder()]);
        $object = new SumExample(1, 2);
        $actual = $serializer->serialize($object, 'json', ['groups' => ['base', 'get']]);
        $this->assertEquals(
            [
                'addition' => 3,
            ],
            json_decode($actual, true)
        );
    }
}
