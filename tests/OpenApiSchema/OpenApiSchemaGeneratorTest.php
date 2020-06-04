<?php


namespace W2w\Test\Apie\OpenApiSchema;

use DateTimeInterface;
use erasys\OpenApi\Spec\v3\Discriminator;
use erasys\OpenApi\Spec\v3\Schema;
use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Core\SearchFilters\PhpPrimitive;
use W2w\Lib\Apie\DefaultApie;
use W2w\Lib\Apie\Interfaces\ValueObjectInterface;
use W2w\Lib\Apie\OpenApiSchema\OpenApiSchemaGenerator;
use W2w\Lib\Apie\Plugins\StatusCheck\ApiResources\Status;
use W2w\Lib\Apie\Plugins\ValueObject\Schema\ValueObjectSchemaBuilder;
use W2w\Lib\ApieObjectAccessNormalizer\ObjectAccess\GroupedObjectAccess;
use W2w\Lib\ApieObjectAccessNormalizer\ObjectAccess\ObjectAccess;
use W2w\Test\Apie\Mocks\AbstractTestClassForSchemaGenerator;
use W2w\Test\Apie\Mocks\ApiResources\SimplePopo;
use W2w\Test\Apie\Mocks\ApiResources\SumExample;
use W2w\Test\Apie\Mocks\ObjectAccess\ObjectAccessForClassWithCollectionClass;
use W2w\Test\Apie\Mocks\ValueObjects\ObjectWithCollection;
use W2w\Test\Apie\OpenApiSchema\Data\MultipleTypesObject;
use W2w\Test\Apie\OpenApiSchema\Data\Polymorphic\ClassA;
use W2w\Test\Apie\OpenApiSchema\Data\Polymorphic\ClassB;
use W2w\Test\Apie\OpenApiSchema\Data\Polymorphic\ClassC;
use W2w\Test\Apie\OpenApiSchema\Data\Polymorphic\TestInterface;
use W2w\Test\Apie\OpenApiSchema\Data\Polymorphic\TestObject;
use W2w\Test\Apie\OpenApiSchema\Data\RecursiveObject;

class OpenApiSchemaGeneratorTest extends TestCase
{
    /**
     * @var OpenApiSchemaGenerator
     */
    private $testItem;

    protected function setUp(): void
    {
        $apie = DefaultApie::createDefaultApie();

        $objectAccess = new GroupedObjectAccess(
            new ObjectAccess(),
            [
                new ObjectAccessForClassWithCollectionClass()
            ]
        );

        $this->testItem = new OpenApiSchemaGenerator(
            [
                ValueObjectInterface::class => new ValueObjectSchemaBuilder()
            ],
            $objectAccess,
            $apie->getClassMetadataFactory(),
            $apie->getPropertyTypeExtractor(),
            $apie->getClassResourceConverter(),
            $apie->getPropertyConverter()
        );
        $this->testItem->defineSchemaForResource(
            DateTimeInterface::class,
            new Schema(['type' => 'string', 'format' => 'date-time'])
        );
    }

    public function testCreateSchema()
    {
        $expected = new Schema(
            [
                'title'       => 'SimplePopo',
                'description' => 'SimplePopo get for groups get, read',
                'type'        => 'object',
                'properties'  => [
                    'id'              => new Schema(
                        [
                            'type' => 'string'
                        ]
                    ),
                    'created_at'      => new Schema(
                        [
                            'type'   => 'string',
                            'format' => 'date-time'
                        ]
                    ),
                    'arbitrary_field' => new Schema([])
                ]
            ]
        );

        $this->assertEquals(
            $expected,
            $this->testItem->createSchema(SimplePopo::class, 'get', ['get', 'read'])
        );
        $this->assertEquals(
            $expected,
            $this->testItem->createSchema(SimplePopo::class, 'get', ['get', 'read']),
            'asking again gives a cached result'
        );

        $expected = new Schema(
            [
                'title'       => 'SimplePopo',
                'description' => 'SimplePopo post for groups post, write',
                'type'        => 'object',
                'properties'  => [
                    'arbitrary_field' => new Schema([])
                ]
            ]
        );

        $this->assertEquals(
            $expected,
            $this->testItem->createSchema(SimplePopo::class, 'post', ['post', 'write'])
        );
        $this->assertEquals(
            $expected,
            $this->testItem->createSchema(SimplePopo::class, 'post', ['post', 'write']),
            'asking again gives a cached result'
        );

        $expected = new Schema(
            [
                'title'       => 'SimplePopo',
                'description' => 'SimplePopo put for groups put, write',
                'type'        => 'object',
                'properties'  => [
                    'arbitrary_field' => new Schema([])
                ]
            ]
        );

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

    public function testCreateSchema_status()
    {
        $expected = new Schema(
            [
                'title'       => 'Status',
                'description' => 'Status get for groups get, read',
                'type'        => 'object',
                'properties'  => [
                    'id'              => new Schema(
                        [
                            'type' => 'string'
                        ]
                    ),
                    'status'      => new Schema(
                        [
                            'type'   => 'string',
                            'nullable' => false,
                        ]
                    ),
                    'optional_reference' => new Schema(
                        [
                            'type' => 'string',
                            'nullable' => true,
                        ]
                    ),
                    'context' => new Schema(
                        [
                            'type' => 'array',
                            'nullable' => true,
                            'items' => new Schema([]),
                        ]
                    ),
                    'no_errors' => new Schema(
                        [
                            'type' => 'boolean',
                            'description' => "Returns true to tell the status check is 'healthy'.",
                            'nullable' => false,
                        ]
                    ),
                ]
            ]
        );

        $this->assertEquals(
            $expected,
            $this->testItem->createSchema(Status::class, 'get', ['get', 'read'])
        );
        $this->assertEquals(
            $expected,
            $this->testItem->createSchema(Status::class, 'get', ['get', 'read']),
            'asking again gives a cached result'
        );
    }

    public function testCreateSchema_predefined_schema()
    {
        $this->assertNotEquals(
            new Schema(['type' => 'string']), $this->testItem->createSchema(SimplePopo::class, 'get', ['get', 'read'])
        );
        $this->testItem->defineSchemaForResource(SimplePopo::class, new Schema(['type' => 'string']));
        $this->assertEquals(
            new Schema(['type' => 'string']), $this->testItem->createSchema(SimplePopo::class, 'get', ['get', 'read'])
        );
        $this->assertEquals(
            new Schema(['type' => 'string']), $this->testItem->createSchema(SimplePopo::class, 'post', ['post', 'read'])
        );
    }

    public function testCreateSchema_recursive_object()
    {
        $wrap = function (Schema $schema) {
            return new Schema([
                'title' => 'RecursiveObject',
                'description' => 'RecursiveObject get for groups get, read',
                'type'        => 'object',
                'properties'  => [
                    'child' => $schema
                ],
            ]);
        };
        $schema = new Schema([
            'title' => 'RecursiveObject',
            'description' => 'RecursiveObject get for groups get, read',
            'type'        => 'object',
            'additionalProperties' => true,
        ]);
        $expected = $wrap($wrap($wrap($schema)));

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
            'additionalProperties' => true,
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
            'additionalProperties' => true,
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
            'additionalProperties' => true,
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
            'title' => 'TestObject',
            'description' => 'TestObject get for groups get, read',
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
            'title' => 'ClassA',
            'description' => 'ClassA get for groups get, read',
            'properties' => [
                'type' => new Schema(['type' => 'string', 'nullable' => false, 'default' => 'A', 'example' => 'A']),
                'required_in_interface' => new Schema(['type' => 'string', 'nullable' => false]),
                'this_is_a' => new Schema(['type' => 'string', 'nullable' => false]),
            ],
        ]);
        $schemaB = new Schema([
            'type' => 'object',
            'title' => 'ClassB',
            'description' => 'ClassB get for groups get, read',
            'properties' => [
                'type' => new Schema(['type' => 'string', 'nullable' => false, 'default' => 'B', 'example' => 'B']),
                'required_in_interface' => new Schema(['type' => 'string', 'nullable' => false]),
                'this_is_b' => new Schema(['type' => 'string', 'nullable' => true]),
                'b_or_c' => new Schema(['type' => 'string', 'nullable' => false]),
            ],
        ]);
        $schemaC = new Schema([
            'type' => 'object',
            'title' => 'ClassC',
            'description' => 'ClassC get for groups get, read',
            'properties' => [
                'type' => new Schema(['type' => 'string', 'nullable' => false, 'default' => 'C', 'example' => 'C']),
                'required_in_interface' => new Schema(['type' => 'string', 'nullable' => false]),
                'b_or_c' => new Schema(['type' => 'integer', 'nullable' => false]),
            ],
        ]);
        $schemaD = new Schema([
           'type' => 'object',
           'title' => 'ClassC',
           'description' => 'ClassC get for groups get, read',
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
                $schemaD,
            ],
            'discriminator' => new Discriminator(
                'type',
                [
                    'A' => $schemaA,
                    'B' => $schemaB,
                    'C' => $schemaC,
                    'D' => $schemaD
                ]
            )
        ]);
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

    public function testCreateSchema_typed_collection()
    {
        $sumExampleSchema = $this->testItem->createSchema(SumExample::class, 'get', ['get', 'read']);
        $expected = new Schema([
            'type' => 'object',
            'title' => 'ObjectWithCollection',
            'description' => 'ObjectWithCollection get for groups get, read',
            'properties' => [
                'collection' => new Schema([
                    'type' => 'array',
                    'items' => $sumExampleSchema,
                    'nullable' => false,
                ]),
                'addition' => new Schema([
                    'type' => 'integer',
                    'description' => 'Sum of all',
                    'nullable' => false,
                ]),
                'optional_collection' => new Schema([
                    'type' => 'array',
                    'items' => $sumExampleSchema,
                    'nullable' => true,
                ]),
            ],
        ]);
        $this->assertEquals($expected, $this->testItem->createSchema(ObjectWithCollection::class, 'get', ['get', 'read']));

        $sumExampleSchema = $this->testItem->createSchema(SumExample::class, 'post', ['post', 'write']);
        $sumExampleSchema->description = 'SumExample post for groups put, write';
        $expected = new Schema([
            'type' => 'object',
            'title' => 'ObjectWithCollection',
            'description' => 'ObjectWithCollection put for groups put, write',
            'properties' => [
                'collection' => new Schema([
                    'type' => 'array',
                    'items' => $sumExampleSchema,
                    'nullable' => false,
                ]),
                'optional_collection' => new Schema([
                    'type' => 'array',
                    'items' => $sumExampleSchema,
                    'nullable' => true,
                ]),
            ],
        ]);
        $this->assertEquals($expected, $this->testItem->createSchema(ObjectWithCollection::class, 'put', ['put', 'write']));
    }
}

