<?php
namespace W2w\Test\Apie\Core\SearchFilters;

use erasys\OpenApi\Spec\v3\Schema;
use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Core\SearchFilters\PhpPrimitive;
use W2w\Lib\Apie\Exceptions\InvalidReturnTypeOfApiResourceException;

class PhpPrimitiveTest extends TestCase
{
    /**
     * @dataProvider getSchemaForFilterProvider
     */
    public function testGetSchemaForFilter(Schema $expected, string $input)
    {
        $testItem = PhpPrimitive::fromNative($input);
        $this->assertEquals($expected, $testItem->getSchemaForFilter());
    }

    public function getSchemaForFilterProvider()
    {
        $boolean = new Schema(['type' => 'boolean']);
        $int = new Schema(['type' => 'number', 'format' => 'int32']);
        $float = new Schema(['type' => 'number', 'format' => 'double']);
        $string = new Schema(['type' => 'string']);

        yield [$string, PhpPrimitive::STRING];
        yield [$boolean, PhpPrimitive::BOOL];
        yield [$int, PhpPrimitive::INT];
        yield [$float, PhpPrimitive::FLOAT];
    }

    /**
     * @dataProvider convertProvider
     */
    public function testConvert($expected, string $input, string $enum)
    {
        $testItem = PhpPrimitive::fromNative($enum);
        $this->assertEquals($expected, $testItem->convert($input));
    }

    public function convertProvider()
    {
        yield ['', '', PhpPrimitive::STRING];
        yield [false, '', PhpPrimitive::BOOL];
        yield [true, 'on', PhpPrimitive::BOOL];
        yield [42, '42', PhpPrimitive::INT];
        yield [1.5, '1.5', PhpPrimitive::FLOAT];
    }

    /**
     * @dataProvider convertFailedProvider
     */
    public function testConvert_failure(string $input, string $enum)
    {
        $testItem = PhpPrimitive::fromNative($enum);
        $this->expectException(InvalidReturnTypeOfApiResourceException::class);
        $testItem->convert($input);
    }

    public function convertFailedProvider()
    {
        yield ['this value is not a boolean', PhpPrimitive::BOOL];
        yield ['42.0', PhpPrimitive::INT];
        yield ['42.5', PhpPrimitive::INT];
        yield ['this value is not a float', PhpPrimitive::FLOAT];
        yield ['1junk', PhpPrimitive::FLOAT];
        yield ['', PhpPrimitive::INT];
    }
}
