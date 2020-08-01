<?php
namespace W2w\Lib\Apie\Core\SearchFilters;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use W2w\Lib\Apie\Interfaces\ValueObjectInterface;

class SearchFilterHelper
{
    static public function applyPaginationToSearchFilter(
        array $input,
        SearchFilterRequest  $searchFilterRequest,
        PropertyAccessorInterface  $accessor
    ): Pagerfanta {
        $paginator = new Pagerfanta(new ArrayAdapter(
            array_values(array_filter($input, function ($item) use ($searchFilterRequest, $accessor) {
                return self::filter($accessor, $item, $searchFilterRequest);
            }))
        ));
        $searchFilterRequest->updatePaginator($paginator);
        return $paginator;
    }

    static private function filter(
        PropertyAccessorInterface  $accessor,
        $item,
        SearchFilterRequest $searchFilterRequest
    ): bool {
        foreach ($searchFilterRequest->getSearches() as $name => $value) {
            $foundValue = $accessor->getValue($item, $name);
            if ($foundValue instanceof ValueObjectInterface) {
                $foundValue = $foundValue->toNative();
            }
            if ($foundValue !== $value) {
                return false;
            }
        }
        return true;
    }

    /**
     * Applies pagination and search on an array.
     *
     * @param array $input
     * @param SearchFilterRequest $searchFilterRequest
     * @param PropertyAccessorInterface $accessor
     * @return array
     */
    static public function applySearchFilter(
        array $input,
        SearchFilterRequest $searchFilterRequest,
        PropertyAccessorInterface $accessor
    ) {
        $count = 0;
        $offset = $searchFilterRequest->getOffset();
        $max = $offset + $searchFilterRequest->getNumberOfItems();
        return array_values(array_filter($input, function ($item) use (&$count, $searchFilterRequest, $max, $offset, $accessor) {
            if ($count >= $max) {
                return false;
            }
            if (!self::filter($accessor, $item, $searchFilterRequest)) {
                return false;
            }
            $count++;
            return ($count > $offset);
        }));
    }
}
