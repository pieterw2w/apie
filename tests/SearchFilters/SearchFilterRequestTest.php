<?php
namespace W2w\Test\Apie\SearchFilters;

use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Exceptions\InvalidPageLimitException;
use W2w\Lib\Apie\Exceptions\PageIndexShouldNotBeNegativeException;
use W2w\Lib\Apie\SearchFilters\SearchFilter;
use W2w\Lib\Apie\SearchFilters\SearchFilterRequest;
use W2w\Lib\Apie\ValueObjects\PhpPrimitive;

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

    /**
     * @dataProvider applySearchFilterProvider
     */
    public function testApplySearchFilter(array $expected, array $searches, SearchFilter $searchFilter)
    {
        $testItem = new SearchFilterRequest(0, 20, $searches);
        $this->assertEquals($testItem, $testItem->applySearchFilter($searchFilter));
        $this->assertSame($expected, $testItem->getSearches());
    }

    public function applySearchFilterProvider()
    {
        $searches = ['test' => '12', 'pizza' => 'Salami'];
        yield [[], $searches, new SearchFilter()];

        $filter = new SearchFilter();
        $filter->addPrimitiveSearchFilter('test', PhpPrimitive::INT);
        $filter->addPrimitiveSearchFilter('pizza', PhpPrimitive::STRING);

        yield [['test' => 12, 'pizza' => 'Salami'], $searches, $filter];

        $filter = new SearchFilter();
        $filter->addPrimitiveSearchFilter('test', PhpPrimitive::FLOAT);
        $filter->addPrimitiveSearchFilter('pizza', PhpPrimitive::STRING);

        yield [['test' => 12.0, 'pizza' => 'Salami'], $searches, $filter];
    }
}
