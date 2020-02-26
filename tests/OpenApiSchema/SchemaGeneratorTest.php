<?php
namespace W2w\Test\Apie\OpenApiSchema;

use DateTimeInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use erasys\OpenApi\Spec\v3\Discriminator;
use erasys\OpenApi\Spec\v3\Schema;
use PHPUnit\Framework\TestCase;
use Pjordaan\AlternateReflectionExtractor\ReflectionExtractor;
use Prophecy\Argument;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Mapping\Loader\LoaderChain;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use W2w\Lib\Apie\Core\ClassResourceConverter;
use W2w\Lib\Apie\Core\SearchFilters\PhpPrimitive;
use W2w\Lib\Apie\OpenApiSchema\SchemaGenerator;
use W2w\Lib\Apie\Plugins\Core\Serializers\Mapping\BaseGroupLoader;
use W2w\Test\Apie\Mocks\AbstractTestClassForSchemaGenerator;
use W2w\Test\Apie\Mocks\ApiResources\SimplePopo;
use W2w\Test\Apie\OpenApiSchema\Data\MultipleTypesObject;
use W2w\Test\Apie\OpenApiSchema\Data\Polymorphic\ClassA;
use W2w\Test\Apie\OpenApiSchema\Data\Polymorphic\ClassB;
use W2w\Test\Apie\OpenApiSchema\Data\Polymorphic\ClassC;
use W2w\Test\Apie\OpenApiSchema\Data\Polymorphic\TestInterface;
use W2w\Test\Apie\OpenApiSchema\Data\Polymorphic\TestObject;
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

    public function testCreateSchema_value_objects()
    {
        $expected = new Schema([
            'type' => 'string',
            'format' => 'php_primitive',
            'enum' => array_values(PhpPrimitive::getValidValues())
        ]);
        $this->assertEquals(
            $expected,
            $this->testItem->createSchema(PhpPrimitive::class, 'get', ['get', 'read'])
        );
        $this->assertEquals(
            $expected,
            $this->testItem->createSchema(PhpPrimitive::class, 'get', ['get', 'read']),
            'asking again gives a cached result'
        );
    }

    public function testCreateSchema_interface()
    {
        $expected = new Schema([
            'type' => 'object',
            'title' => TestInterface::class,
            'description' => TestInterface::class . ' get for groups get, read',
            'properties' => [
                'type' => new Schema(['type' => 'string', 'nullable' => false]),
                'required_in_interface' => new Schema(['type' => 'string', 'nullable' => false]),
            ],
        ]);
        $this->assertEquals(
            $expected,
            $this->testItem->createSchema(TestInterface::class, 'get', ['get', 'read'])
        );
        $this->assertEquals(
            $expected,
            $this->testItem->createSchema(TestInterface::class, 'get', ['get', 'read']),
            'asking again gives a cached result'
        );
    }

    public function testCreateSchema_abstract_class()
    {
        $expected = new Schema([
            'type' => 'object',
            'title' => AbstractTestClassForSchemaGenerator::class,
            'description' => AbstractTestClassForSchemaGenerator::class . ' get for groups get, read',
            'properties' => [
                'value' => new Schema(['type' => 'string', 'nullable' => true]),
            ],
        ]);
        $this->assertEquals(
            $expected,
            $this->testItem->createSchema(AbstractTestClassForSchemaGenerator::class, 'get', ['get', 'read'])
        );
        $this->assertEquals(
            $expected,
            $this->testItem->createSchema(AbstractTestClassForSchemaGenerator::class, 'get', ['get', 'read']),
            'asking again gives a cached result'
        );

        $expected = new Schema([
            'type' => 'object',
            'title' => AbstractTestClassForSchemaGenerator::class,
            'description' => AbstractTestClassForSchemaGenerator::class . ' post for groups post, write',
            'properties' => [
                'another_value' => new Schema(['type' => 'string', 'nullable' => true]),
            ],
        ]);
        $this->assertEquals(
            $expected,
            $this->testItem->createSchema(AbstractTestClassForSchemaGenerator::class, 'post', ['post', 'write'])
        );
        $this->assertEquals(
            $expected,
            $this->testItem->createSchema(AbstractTestClassForSchemaGenerator::class, 'post', ['post', 'write']),
            'asking again gives a cached result'
        );
    }

    public function testDefineSchemaForPolymorphicObject()
    {
        $expected = $this->createPolymorphicObjectSchema();
        $this->assertEquals(
            $expected,
            $this->testItem->defineSchemaForPolymorphicObject(
                TestInterface::class,
                'type',
                [
                    'A' => ClassA::class,
                    'B' => ClassB::class,
                    'C' => ClassC::class,
                    'D' => ClassC::class
                ],
                'get',
                ['get', 'read']
            )
        );
        $this->assertEquals(
            $expected,
            $this->testItem->createSchema(TestInterface::class, 'get', ['get', 'read']),
            'running createSchema gives the created result'
        );
    }

    public function testCreateSchemaWithPolymorphicObject()
    {
        $this->testItem->defineSchemaForPolymorphicObject(
            TestInterface::class,
            'type',
            [
                'A' => ClassA::class,
                'B' => ClassB::class,
                'C' => ClassC::class,
                'D' => ClassC::class
            ],
            'get',
            ['get', 'read']
        );

        $polymorphicSchema = $this->createPolymorphicObjectSchema();

        $expected = new Schema([
            'type' => 'object',
            'title' => TestObject::class,
            'description' => TestObject::class  . ' get for groups get, read',
            'properties' => [
                'item' => $polymorphicSchema,
                'list' => new Schema([
                    'type' => 'array',
                    'nullable' => false,
                    'items' => $polymorphicSchema,
                ]),
            ],
        ]);

        $this->assertEquals(
            $expected,
            $this->testItem->createSchema(TestObject::class, 'get', ['get', 'read'])
        );
    }

    private function createPolymorphicObjectSchema(): Schema
    {
        $schemaA = new Schema([
            'type' => 'object',
            'title' => ClassA::class,
            'description' => ClassA::class . ' get for groups get, read',
            'properties' => [
                'type' => new Schema(['type' => 'string', 'nullable' => false, 'default' => 'A', 'example' => 'A']),
                'required_in_interface' => new Schema(['type' => 'string', 'nullable' => false]),
                'this_is_a' => new Schema(['type' => 'string', 'nullable' => false]),
            ],
        ]);
        $schemaB = new Schema([
            'type' => 'object',
            'title' => ClassB::class,
            'description' => ClassB::class . ' get for groups get, read',
            'properties' => [
                'type' => new Schema(['type' => 'string', 'nullable' => false, 'default' => 'B', 'example' => 'B']),
                'required_in_interface' => new Schema(['type' => 'string', 'nullable' => false]),
                'this_is_b' => new Schema(['type' => 'string', 'nullable' => true]),
                'b_or_c' => new Schema(['type' => 'string', 'nullable' => false]),
            ],
        ]);
        $schemaC = new Schema([
            'type' => 'object',
            'title' => ClassC::class,
            'description' => ClassC::class . ' get for groups get, read',
            'properties' => [
                'type' => new Schema(['type' => 'string', 'nullable' => false, 'default' => 'D', 'example' => 'D']),
                'required_in_interface' => new Schema(['type' => 'string', 'nullable' => false]),
                'b_or_c' => new Schema(['type' => 'integer', 'nullable' => false]),
            ],
        ]);

        return new Schema([
            'type' => 'object',
            'properties' => [
                'type' => new Schema(['type' => 'string']),
            ],
            'oneOf' => [
                $schemaA,
                $schemaB,
                $schemaC,
            ],
            'discriminator' => new Discriminator(
                'type',
                [
                    'A' => $schemaA,
                    'B' => $schemaB,
                    'C' => $schemaC,
                    'D' => $schemaC
                ]
            )
        ]);
    }

    public function testCreateSchema_multiple_types()
    {
        $simplePopoSchema = $this->testItem->createSchema(SimplePopo::class, 'get', ['get', 'read']);
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
                        'items' => $simplePopoSchema,
                    ]),
                    'name' => new Schema(['type' => 'string']),
                    'value_object' => new Schema(['type' => 'string', 'format' => 'value_object']),
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
