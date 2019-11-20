<?php


namespace W2w\Lib\Apie\Retrievers;


use W2w\Lib\Apie\Models\ApiResourceClassMetadata;
use W2w\Lib\Apie\SearchFilters\SearchFilter;

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
