<?php
namespace W2w\Test\Apie\OpenApiSchema;

use DateTimeInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use erasys\OpenApi\Spec\v3\Schema;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Mapping\Loader\LoaderChain;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use W2w\Lib\Apie\Core\ClassResourceConverter;
use W2w\Lib\Apie\Core\SearchFilters\PhpPrimitive;
use W2w\Lib\Apie\Interfaces\ValueObjectInterface;
use W2w\Lib\Apie\OpenApiSchema\OpenApiSchemaGenerator;
use W2w\Lib\Apie\Plugins\Core\Serializers\Mapping\BaseGroupLoader;
use W2w\Lib\Apie\Plugins\ValueObject\Schema\ValueObjectSchemaBuilder;
use W2w\Lib\ApieObjectAccessNormalizer\ObjectAccess\ObjectAccess;
use W2w\Test\Apie\Mocks\AbstractTestClassForSchemaGenerator;
use W2w\Test\Apie\Mocks\ApiResources\SimplePopo;
use W2w\Test\Apie\OpenApiSchema\Data\MultipleTypesObject;
use W2w\Test\Apie\OpenApiSchema\Data\Polymorphic\TestInterface;
use W2w\Test\Apie\OpenApiSchema\Data\RecursiveObject;

class SchemaGeneratorTest extends TestCase
{
    /**
     * @var OpenApiSchemaGenerator
     */
    private $testItem;

    protected function setUp(): void
    {
        AnnotationRegistry::registerLoader('class_exists');

        $classResourceConverter = $this->prophesize(ClassResourceConverter::class);
        $classResourceConverter->normalize(Argument::any())->willReturnArgument(0);
        $classResourceConverter->denormalize(Argument::any())->willReturnArgument(0);

        $classMetadataFactory = new ClassMetadataFactory(
            new LoaderChain([
                new AnnotationLoader(new AnnotationReader()),
                new BaseGroupLoader(['read', 'write', 'get', 'post', 'put']),
            ])
        );

        $this->testItem = new OpenApiSchemaGenerator(
            [
                ValueObjectInterface::class => new ValueObjectSchemaBuilder()
            ],
            new ObjectAccess(true),
            $classMetadataFactory,
            new MetadataAwareNameConverter($classMetadataFactory, new CamelCaseToSnakeCaseNameConverter())
        );

        $this->testItem->defineSchemaForResource(
            DateTimeInterface::class,
            new Schema(['type' => 'string', 'format' => 'date-time'])
        );
    }

    public function testCreateSchema()
    {
        $arbitraryField = new Schema([
            'type' => 'object',
            'additionalProperties' => true,
        ]);

        $expected = new Schema([
            'title' => 'SimplePopo',
            'description' => 'SimplePopo get for groups get, read',
            'type' => 'object',
            'properties' => [
                'id' => new Schema([
                    'type' => 'string'
                ]),
                'created_at' => new Schema([
                    'type' => 'string',
                    'format' => 'date-time'
                ]),
                'arbitrary_field' => $arbitraryField
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
            'title' => 'SimplePopo',
            'description' => 'SimplePopo post for groups post, write',
            'type' => 'object',
            'properties' => [
                'arbitrary_field' => $arbitraryField
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
            'title' => 'SimplePopo',
            'description' => 'SimplePopo put for groups put, write',
            'type' => 'object',
            'properties' => [
                'arbitrary_field' => $arbitraryField
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
        $wrap = function (?Schema $schema) {
            return new Schema([
                'title' => 'RecursiveObject',
                'description' => 'RecursiveObject get for groups get, read',
                'type'        => 'object',
                'properties'  => $schema ? [ 'child' => $schema ] : [],
            ]);
        };
        $expected = $wrap($wrap($wrap(null)));

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
            'title' => 'TestInterface',
            'description' => 'TestInterface get for groups get, read',
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
            'title' => 'AbstractTestClassForSchemaGenerator',
            'description' => 'AbstractTestClassForSchemaGenerator get for groups get, read',
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
            'title' => 'AbstractTestClassForSchemaGenerator',
            'description' => 'AbstractTestClassForSchemaGenerator post for groups post, write',
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

    public function testCreateSchema_multiple_types()
    {
        $simplePopoSchema = $this->testItem->createSchema(SimplePopo::class, 'get', ['get', 'read']);
        $expected = new Schema(
            [
                'title'       => 'MultipleTypesObject',
                'description' => 'MultipleTypesObject get for groups get, read',
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
