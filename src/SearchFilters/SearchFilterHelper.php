<?php
namespace W2w\Lib\Apie\SearchFilters;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use W2w\Lib\Apie\ValueObjects\ValueObjectInterface;

class SearchFilterHelper
{
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
            foreach ($searchFilterRequest->getSearches() as $name => $value) {
                $foundValue = $accessor->getValue($item, $name);
                if ($foundValue instanceof ValueObjectInterface) {
                    $foundValue = $foundValue->toNative();
                }
                if ($foundValue !== $value) {
                    return false;
                }
            }
            $count++;
            return ($count > $offset);
        }));
    }
}
