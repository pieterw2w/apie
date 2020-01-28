<?php
namespace W2w\Test\Apie\Plugins\Core\Encodings;

use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Plugins\Core\Encodings\FormatRetriever;

class FormatRetrieverTest extends TestCase
{
    private $testItem;

    protected function setUp(): void
    {
        $this->testItem = new FormatRetriever([
            'application/json' => 'json',
            'application/xml' => 'xml',
        ]);
    }

    /**
     * @dataProvider  getFormatProvider
     */
    public function testGetFormat(?string $expected, string $input)
    {
        $this->assertEquals($expected, $this->testItem->getFormat($input));
    }

    public function getFormatProvider()
    {
        yield ['xml', 'application/xml'];
        yield ['json', 'application/json'];
        yield [null, 'text/json'];
    }

    /**
     * @dataProvider  getContentTypeProvider
     */
    public function testGetContentType(?string $expected, string $input)
    {
        $this->assertEquals($expected, $this->testItem->getContentType($input));
    }

    public function getContentTypeProvider()
    {
        yield ['application/xml', 'xml'];
        yield ['application/json', 'json'];
        yield [null, 'html'];
    }
}
