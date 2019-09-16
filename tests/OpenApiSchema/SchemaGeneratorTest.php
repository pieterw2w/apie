<?php
namespace W2w\Test\Apie\OpenApiSchema;

use DateTimeInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\Reader;
use erasys\OpenApi\Spec\v3\Schema;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Mapping\Loader\LoaderChain;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use W2w\Lib\Apie\BaseGroupLoader;
use W2w\Lib\Apie\ClassResourceConverter;
use W2w\Lib\Apie\OpenApiSchema\SchemaGenerator;
use W2w\Test\Apie\Mocks\Data\SimplePopo;
use W2w\Test\Apie\OpenApiSchema\Data\MultipleTypesObject;
use W2w\Test\Apie\OpenApiSchema\Data\RecursiveObject;

class SchemaGeneratorTest extends TestCase
{
    /**
     * @var SchemaGenerator
     */
    private $testItem;

    protected function setUp(): void
    {
        AnnotationRegistry::registerLoader('class_exists');
        // a full list of extractors is shown further below
        $phpDocExtractor = new PhpDocExtractor();
        $reflectionExtractor = new ReflectionExtractor();

        // list of PropertyListExtractorInterface (any iterable)
        $listExtractors = [$reflectionExtractor];

        // list of PropertyTypeExtractorInterface (any iterable)
        $typeExtractors = [$phpDocExtractor, $reflectionExtractor];

        // list of PropertyDescriptionExtractorInterface (any iterable)
        $descriptionExtractors = [$phpDocExtractor];

        // list of PropertyAccessExtractorInterface (any iterable)
        $accessExtractors = [$reflectionExtractor];

        // list of PropertyInitializableExtractorInterface (any iterable)
        $propertyInitializableExtractors = [$reflectionExtractor];

        $propertyInfo = new PropertyInfoExtractor(
            $listExtractors,
            $typeExtractors,
            $descriptionExtractors,
            $accessExtractors,
            $propertyInitializableExtractors
        );

        $classResourceConverter = $this->prophesize(ClassResourceConverter::class);
        $classResourceConverter->normalize(Argument::any())->willReturnArgument(0);
        $classResourceConverter->denormalize(Argument::any())->willReturnArgument(0);

        $classMetadataFactory = new ClassMetadataFactory(
            new LoaderChain([
                new AnnotationLoader(new AnnotationReader()),
                new BaseGroupLoader(['read', 'write', 'get', 'post', 'put']),
            ])
        );

        $this->testItem = new SchemaGenerator(
            $classMetadataFactory,
            $propertyInfo,
            $classResourceConverter->reveal(),
            new MetadataAwareNameConverter($classMetadataFactory, new CamelCaseToSnakeCaseNameConverter())
        );

        $this->testItem->defineSchemaForResource(
            DateTimeInterface::class,
            new Schema(['type' => 'string', 'format' => 'date-time'])
        );
    }

    public function testCreateSchema()
    {
        $expected = new Schema([
            'title' => SimplePopo::class,
            'description' => SimplePopo::class . ' get for groups get, read',
            'type' => 'object',
            'properties' => [
                'id' => new Schema([
                    'type' => 'string'
                ]),
                'created_at' => new Schema([
                    'type' => 'string',
                    'format' => 'date-time'
                ]),
                'arbitrary_field' => new Schema([
                    'type' => 'string',
                    'nullable' => true
                ])
            ]
        ]);

        $this->assertEquals(
            $expected,
            $this->testItem->createSchema(SimplePopo::class, 'get', ['get', 'read'])
        );
        $this->assertEquals(
            $expected,
            $this->testItem->createSchema(SimplePopo::class, 'get', ['get', 'read']),
            'asking again gives a cached result'
        );

        $expected = new Schema([
            'title' => SimplePopo::class,
            'description' => SimplePopo::class . ' post for groups post, write',
            'type' => 'object',
            'properties' => [
                'arbitrary_field' => new Schema([
                    'type' => 'string',
                    'nullable' => true
                ])
            ]
        ]);

        $this->assertEquals(
            $expected,
            $this->testItem->createSchema(SimplePopo::class, 'post', ['post', 'write'])
        );
        $this->assertEquals(
            $expected,
            $this->testItem->createSchema(SimplePopo::class, 'post', ['post', 'write']),
            'asking again gives a cached result'
        );

        $expected = new Schema([
            'title' => SimplePopo::class,
            'description' => SimplePopo::class . ' put for groups put, write',
            'type' => 'object',
            'properties' => [
                'arbitrary_field' => new Schema([
                    'type' => 'string',
                    'nullable' => true
                ])
            ]
        ]);

        $this->assertEquals(
            $expected,
            $this->testItem->createSchema(SimplePopo::class, 'put', ['put', 'write'])
        );
        $this->assertEquals(
            $expected,
            $this->testItem->createSchema(SimplePopo::class, 'put', ['put', 'write']),
            'asking again gives a cached result'
        );
    }

    public function testCreateSchema_predefined_schema()
    {
        $this->assertNotEquals(new Schema(['type' => 'string']), $this->testItem->createSchema(SimplePopo::class, 'get', ['get', 'read']));
        $this->testItem->defineSchemaForResource(SimplePopo::class, new Schema(['type' => 'string']));
        $this->assertEquals(new Schema(['type' => 'string']), $this->testItem->createSchema(SimplePopo::class, 'get', ['get', 'read']));
        $this->assertEquals(new Schema(['type' => 'string']), $this->testItem->createSchema(SimplePopo::class, 'post', ['post', 'read']));
        $this->assertEquals(new Schema(['type' => 'string']), $this->testItem->createSchema(SimplePopo::class, 'put', ['put', 'read']));
    }

    public function testCreateSchema_recursive_object()
    {
        $wrap = function (Schema $schema) {
            return new Schema([
                'title' => RecursiveObject::class,
                'description' => RecursiveObject::class . ' get for groups get, read',
                'type'        => 'object',
                'properties'  => [
                    'child' => $schema
                ],
            ]);
        };
        $expected = $wrap($wrap($wrap(new Schema(['type' => 'object', 'nullable' => false]))));

        $this->assertEquals(
            $expected,
            $this->testItem->createSchema(RecursiveObject::class, 'get', ['get', 'read'])
        );
        $this->assertEquals(
            $expected,
            $this->testItem->createSchema(RecursiveObject::class, 'get', ['get', 'read']),
            'asking again gives a cached result'
        );
    }

    public function testCreateSchema_multiple_types()
    {
        $expected = new Schema(
            [
                'title'       => MultipleTypesObject::class,
                'description' => MultipleTypesObject::class . ' get for groups get, read',
                'type'        => 'object',
                'properties'  => [
                    'floating_point' => new Schema(['type' => 'number', 'format' => 'float']),
                    'double' => new Schema(['type' => 'number', 'format' => 'float']),
                    'integer' => new Schema(['type' => 'integer']),
                    'boolean' => new Schema(['type' => 'boolean']),
                    'array' => new Schema([
                        'type' => 'array',
                        'items' => new Schema([
                            'oneOf' => [
                                new Schema(['type' => 'string', 'nullable' => true]),
                                new Schema(['type' => 'integer']),
                                new Schema(['type' => 'boolean']),
                            ],
                        ])
                    ]),
                    'string_array' => new Schema([
                        'type' => 'array',
                        'items' => new Schema(['type' => 'string']),
                    ]),
                    'object_array' => new Schema([
                        'type' => 'array',
                        'items' => new Schema([
                            'type' => 'object'
                            // TODO: where are the properties of SimplePopo?
                        ])
                    ]),
                    'name' => new Schema(['type' => 'string']),
                ]
            ]
        );

        $this->assertEquals(
            $expected,
            $this->testItem->createSchema(MultipleTypesObject::class, 'get', ['get', 'read'])
        );
        $this->assertEquals(
            $expected,
            $this->testItem->createSchema(MultipleTypesObject::class, 'get', ['get', 'read']),
            'asking again gives a cached result'
        );
    }
}
