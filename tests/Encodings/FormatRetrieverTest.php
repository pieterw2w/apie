<?php
namespace W2w\Test\Apie\Encodings;

use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Encodings\FormatRetriever;

class FormatRetrieverTest extends TestCase
{
    /**
     * @dataProvider  getFormatProvider
     */
    public function testGetFormat(string $expected, string $input)
    {
        $testItem = new FormatRetriever();
        $this->assertEquals($expected, $testItem->getFormat($input));
    }

    public function getFormatProvider()
    {
        yield ['xml', 'application/xml'];
        yield ['xml', 'text/xml'];
        yield ['xml', 'application/xhtml+xml'];
        yield ['xml', 'application/atom+xml'];
        yield ['xml', 'application/xslt+xml'];
        yield ['xml', 'image/svg+xml'];
        yield ['xml', 'application/mathml+xml'];
        yield ['xml', 'application/rss+xml'];
        yield ['json', 'application/json'];
        yield ['json', 'application/ld+json'];
        yield ['json', 'text/json'];
    }

    /**
     * @dataProvider  getContentTypeProvider
     */
    public function testGetContentType(string $expected, string $input)
    {
        $testItem = new FormatRetriever();
        $this->assertEquals($expected, $testItem->getContentType($input));
    }

    public function getContentTypeProvider()
    {
        yield ['application/xml', 'xml'];
        yield ['application/json', 'json'];
        yield ['application/json', 'html'];
    }
}
