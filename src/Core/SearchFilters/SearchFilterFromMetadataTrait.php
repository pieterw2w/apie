<?php

namespace W2w\Lib\Apie\Core\SearchFilters;

use W2w\Lib\Apie\Core\Models\ApiResourceClassMetadata;
use W2w\Lib\Apie\Interfaces\SearchFilterProviderInterface;

/**
 * Implementation for SearchFilterProviderInterface to get the search result fields from the api resource metadata.
 *
 * @see SearchFilterProviderInterface
 */
trait SearchFilterFromMetadataTrait
{
    /**
     * Retrieves search filter for an api resource.
     *
     * @param ApiResourceClassMetadata $classMetadata
     * @return SearchFilter
     */
    public function getSearchFilter(ApiResourceClassMetadata $classMetadata): SearchFilter
    {
        $res = new SearchFilter();
        $context = $classMetadata->getContext();
        if (isset($context['search']) && is_array($context['search'])) {
            foreach ($context['search'] as $name => $type) {
                $res->addPrimitiveSearchFilter($name, $type);
            }
        }

        return $res;
    }
}
