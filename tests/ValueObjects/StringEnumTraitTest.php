<?php
namespace W2w\Test\Apie\ValueObjects;

use erasys\OpenApi\Spec\v3\Schema;
use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Exceptions\InvalidValueForValueObjectException;
use W2w\Test\Apie\ValueObjects\Data\MockValueObjectWithStringEnumTrait;

class StringEnumTraitTest extends TestCase
{
    private $testItem;

    protected function setUp(): void
    {
        $this->testItem = new MockValueObjectWithStringEnumTrait('A');
    }

    public function testFromNative()
    {
        $this->assertEquals('A', $this->testItem->fromNative('A')->toNative());
        $this->assertEquals('B', $this->testItem->fromNative('B')->toNative());
        $this->assertEquals('A', $this->testItem->fromNative('CONSTANT_A')->toNative());
        $this->assertEquals('B', $this->testItem->fromNative('CONSTANT_B')->toNative());
        $this->assertEquals('A', $this->testItem->fromNative('CONSTANT_C')->toNative());
        $this->expectException(InvalidValueForValueObjectException::class);
        $this->testItem->fromNative('does not exist');
    }

    public function testToSchema()
    {
        $expected = new Schema([
            'type' => 'string',
            'format' => 'mock_value_object_with_string_enum_trait',
            'enum' => ['A', 'B', 'A']
        ]);
        $this->assertEquals($expected, $this->testItem->toSchema());
    }
}
