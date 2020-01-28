<?php

namespace W2w\Test\Apie\Plugins\ValueObject;

use erasys\OpenApi\Spec\v3\Schema;
use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Apie;
use W2w\Lib\Apie\Plugins\ValueObject\ValueObjectPlugin;
use W2w\Test\Apie\Mocks\ValueObjects\MockValueObjectWithStringEnumTrait;
use W2w\Test\Apie\Mocks\ValueObjects\MockValueObjectWithStringTrait;

class ValueObjectPluginTest extends TestCase
{
    /**
     * @var Apie
     */
    private $apie;

    protected function setUp(): void
    {
        $this->apie = new Apie([new ValueObjectPlugin()], true, null);
    }

    public function test_serializer_works_with_value_object()
    {
        $serializer = $this->apie->getResourceSerializer();
        $actual = $serializer->normalize(
            new MockValueObjectWithStringTrait('pizza'),
            'application/json'
        );
        $this->assertEquals('PIZZA', $actual);
    }

    public function test_schema_is_correct()
    {
        $schemaGenerator = $this->apie->getSchemaGenerator();

        $actual = $schemaGenerator->createSchema(
            MockValueObjectWithStringEnumTrait::class,
            'get', ['get', 'read']
        );
        $this->assertEquals(
            new Schema([
                'type' => 'string',
                'format' => 'mock_value_object_with_string_enum_trait',
                'enum' => [
                    'A', 'B', 'A'
                ]
            ]),
            $actual
        );
    }
}
