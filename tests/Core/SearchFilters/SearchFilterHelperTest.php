<?php
namespace W2w\Test\Apie\Core\SearchFilters;

use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;
use W2w\Lib\Apie\Core\SearchFilters\SearchFilterHelper;
use W2w\Lib\Apie\Core\SearchFilters\SearchFilterRequest;
use W2w\Lib\ApieObjectAccessNormalizer\ObjectAccess\ObjectAccess;

class SearchFilterHelperTest extends TestCase
{
    /**
     * @dataProvider applySearchFilterProvider
     */
    public function testApplySearchFilter(
        array $expected,
        array $input,
        SearchFilterRequest $searchFilterRequest
    ) {
        $this->assertEquals(
            $expected,
            SearchFilterHelper::applySearchFilter($input, $searchFilterRequest, new ObjectAccess())
        );
    }

    public function applySearchFilterProvider()
    {
        yield [
            [],
            [],
            new SearchFilterRequest(0, 20, [])
        ];

        yield [
            $this->createArray(3, 0),
            $this->createArray(12, 0),
            new SearchFilterRequest(0, 3, [])
        ];

        yield [
            $this->createArray(9, 6),
            $this->createArray(12, 0),
            new SearchFilterRequest(2, 3, [])
        ];

        yield [
            [],
            $this->createArray(12, 0),
            new SearchFilterRequest(4, 3, [])
        ];

        yield [
            [
                ['test' => 0, 'counter' => 0],
                ['test' => 0, 'counter' => 3],
                ['test' => 0, 'counter' => 6],
            ],
            $this->createArray(12, 0),
            new SearchFilterRequest(0, 3, ['test' => 0])
        ];
        yield [
            [
                ['test' => 0, 'counter' => 9],
            ],
            $this->createArray(12, 0),
            new SearchFilterRequest(1, 3, ['test' => 0])
        ];
    }

    private function createArray(int $endIndex, int $startIndex = 0): array
    {
        $res = [];
        for ($i = $startIndex; $i < $endIndex; $i++) {
            $res[] = ['test' => $i % 3, 'counter' => $i];
        }
        return $res;
    }
}
