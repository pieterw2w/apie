<?php
namespace W2w\Test\Apie\SearchFilters;

use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Exceptions\NameAlreadyDefinedException;
use W2w\Lib\Apie\Exceptions\NameNotFoundException;
use W2w\Lib\Apie\SearchFilters\SearchFilter;
use W2w\Lib\Apie\ValueObjects\PhpPrimitive;

class SearchFilterTest extends TestCase
{
    public function testGetters()
    {
        $testItem = new SearchFilter();
        $this->assertEquals([], $testItem->getAllPrimitiveSearchFilter());
        $this->assertFalse($testItem->hasPrimitiveSearchFilter('search'));
        $this->assertEquals($testItem, $testItem->addPrimitiveSearchFilter('search', new PhpPrimitive(PhpPrimitive::BOOL)));
        $this->assertTrue($testItem->hasPrimitiveSearchFilter('search'));
        $this->assertEquals(new PhpPrimitive(PhpPrimitive::BOOL), $testItem->getPrimitiveSearchFilter('search'));
        $this->assertEquals(
            ['search' => new PhpPrimitive(PhpPrimitive::BOOL)],
            $testItem->getAllPrimitiveSearchFilter()
        );
    }

    public function test_missing_name()
    {
        $testItem = new SearchFilter();
        $this->expectException(NameNotFoundException::class);
        $testItem->getPrimitiveSearchFilter('pizza');
    }

    public function test_duplicate_name()
    {
        $testItem = new SearchFilter();
        $testItem->addPrimitiveSearchFilter('search', new PhpPrimitive(PhpPrimitive::BOOL));
        $this->expectException(NameAlreadyDefinedException::class);
        $testItem->addPrimitiveSearchFilter('search', new PhpPrimitive(PhpPrimitive::BOOL));
    }
}
