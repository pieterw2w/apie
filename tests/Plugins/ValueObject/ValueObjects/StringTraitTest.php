<?php
namespace W2w\Test\Apie\Plugins\ValueObject\ValueObjects;

use erasys\OpenApi\Spec\v3\Schema;
use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Exceptions\InvalidValueForValueObjectException;
use W2w\Test\Apie\Mocks\ValueObjects\MockValueObjectWithStringTrait;

class StringTraitTest extends TestCase
{
    private $testItem;

    protected function setUp(): void
    {
        $this->testItem = new MockValueObjectWithStringTrait('test');
    }

    public function testFromNative()
    {
        $item = $this->testItem->fromNative('boo');
        $this->assertEquals('BOO', $item->toNative());
        $this->assertEquals('boo', $item->validValueRetrieved);

        $this->expectException(InvalidValueForValueObjectException::class);
        $this->testItem->fromNative('blha');
    }

    public function testToNative()
    {
        $this->assertEquals('TEST', $this->testItem->toNative());
    }

    public function testToSchema()
    {
        $expected = new Schema(['type' => 'string', 'format' => 'mock_value_object_with_string_trait']);
        $this->assertEquals($expected, $this->testItem->toSchema());
    }
}
