<?php
namespace W2w\Lib\Apie\Interfaces;

use W2w\Lib\Apie\Core\Models\ApiResourceClassMetadata;
use W2w\Lib\Apie\Core\SearchFilters\SearchFilter;

/**
 * If a retriever implements this interface as well, it can add search filter arguments.
 */
interface SearchFilterProviderInterface
{
    /**
     * Retrieves search filter for an api resource.
     *
     * @param ApiResourceClassMetadata $classMetadata
     * @return SearchFilter
     */
    public function getSearchFilter(ApiResourceClassMetadata $classMetadata): SearchFilter;
}
