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
use W2w\Lib\Apie\Exceptions\ValidationException;
use W2w\Lib\Apie\Normalizers\ApieObjectNormalizer;
use W2w\Lib\Apie\ServiceLibraryFactory;
use W2w\Test\Apie\Mocks\Data\FullRestObject;
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

    /**
     * @dataProvider denormalizeValidationExceptionProvider
     */
    public function test_denormalize_errors_to_validation_exception(array $expectedErrors, string $outputClass, array $data)
    {
        // this tests requires a properly configured property type extractor, ServiceLibraryFactory provides a
        // proper one, even though this makes it almost a feature test and not a unit test.
        $tmp = (new ServiceLibraryFactory([], false, null));
        $propertyInfoExtractor = $tmp->getPropertyTypeExtractor();
        $normalizer = new ApieObjectNormalizer(
            $tmp->getClassMetadataFactory(),
            new CamelCaseToSnakeCaseNameConverter(),
            PropertyAccess::createPropertyAccessor(),
            $propertyInfoExtractor,
            null,
            null,
            []
        );
        $serializer = new Serializer([new DateTimeNormalizer(['datetime_format' => DateTime::ATOM]), $normalizer], [new JsonEncoder()]);
        try {
            $serializer->denormalize($data, $outputClass, 'json');
            $this->fail('A validation exception should have been thrown!');
        } catch (ValidationException $validationException) {
            $this->assertEquals($expectedErrors, $validationException->getErrors());
        }
    }

    public function denormalizeValidationExceptionProvider()
    {
        yield [
            ['one' => ['one is required']],
            SumExample::class,
            []
        ];
        yield [
            ['one' => ['must be one of "float" ("string" given)']],
            SumExample::class,
            ['one' => 'this is not a number', 'two' => 12]
        ];
        yield [
            ['stringValue' => ['must be one of "string" ("integer" given)']],
            FullRestObject::class,
            ['string_value' => 12]
        ];
        yield [
            ['value' => ['value is required']],
            FullRestObject::class,
            ['value_object' => '']
        ];
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
