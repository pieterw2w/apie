<?php
namespace W2w\Test\Apie\SearchFilters;

use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Exceptions\InvalidPageLimitException;
use W2w\Lib\Apie\Exceptions\PageIndexShouldNotBeNegativeException;
use W2w\Lib\Apie\SearchFilters\SearchFilterRequest;

class SearchFilterRequestTest extends TestCase
{
    public function testGetters()
    {
        $testItem = new SearchFilterRequest(2, 42, []);
        $this->assertEquals(2, $testItem->getPageIndex());
        $this->assertEquals(84, $testItem->getOffset());
        $this->assertEquals(42, $testItem->getNumberOfItems());

    }

    public function test_invalid_page_index()
    {
        $this->expectException(PageIndexShouldNotBeNegativeException::class);
        new SearchFilterRequest(-42, 42, []);
    }

    public function test_invalid_number_of_items()
    {
        $this->expectException(InvalidPageLimitException::class);
        new SearchFilterRequest(0, -42, []);
    }

    public function test_invalid_input()
    {
        $this->expectException(PageIndexShouldNotBeNegativeException::class);
        new SearchFilterRequest(-42, -42, []);
    }
}
