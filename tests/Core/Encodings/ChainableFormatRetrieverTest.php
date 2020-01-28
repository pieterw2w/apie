<?php

namespace W2w\Test\Apie\Core\Encodings;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use W2w\Lib\Apie\Core\Encodings\ChainableFormatRetriever;
use W2w\Lib\Apie\Plugins\Core\Encodings\FormatRetriever;

class ChainableFormatRetrieverTest extends TestCase
{
    /**
     * @var ChainableFormatRetriever
     */
    private $testItem;

    protected function setUp(): void
    {
        $this->testItem = new ChainableFormatRetriever([
            new FormatRetriever([]),
            new FormatRetriever([
                'application/json' => 'json',
                'application/xml' => 'xml',
            ]),
            new FormatRetriever([
                'text/json' => 'json',
                'text/xml' => 'xml',
            ]),
            new FormatRetriever([
                'text/html' => 'html'
            ])
        ]);
    }

    /**
     * @dataProvider getFormatProvider
     */
    public function testGetFormat(string $expected, string $input)
    {
        $this->assertEquals($expected, $this->testItem->getFormat($input));
    }

    public function getFormatProvider()
    {
        yield ['html', 'text/html'];
        yield ['json', 'application/json'];
        yield ['json', 'text/json'];
        yield ['html', 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'];
        yield ['xml', 'application/xml,text/html,application/xhtml+xml;q=0.9,*/*;q=0.8'];
        yield ['xml', 'application/xml;q=0.9,*/*;q=0.8'];
    }

    public function testGetFormat_not_accepted()
    {
        $this->expectException(NotAcceptableHttpException::class);
        $this->testItem->getFormat('pizza');
    }

    /**
     * @dataProvider getContentTypeProvider
     */
    public function testGetContentType(string $expected, string $input)
    {
        $this->assertEquals($expected, $this->testItem->getContentType($input));
    }

    public function getContentTypeProvider()
    {
        yield ['text/html', 'html'];
        yield ['application/json', 'json'];
        yield ['application/xml', 'xml'];
    }

    public function testGetContentType_not_accepted()
    {
        $this->expectException(NotAcceptableHttpException::class);
        $this->testItem->getContentType('application/pizza');
    }
}
