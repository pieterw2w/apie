<?php

namespace W2w\Lib\Apie\Plugins\Mock\Pagers;

use Pagerfanta\Adapter\AdapterInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use W2w\Lib\Apie\Core\SearchFilters\SearchFilterHelper;
use W2w\Lib\Apie\Core\SearchFilters\SearchFilterRequest;
use W2w\Lib\Apie\Plugins\Mock\DataLayers\MockApiResourceDataLayer;

class MockAdapter implements AdapterInterface
{
    /**
     * @var MockApiResourceDataLayer
     */
    private $dataLayer;

    /**
     * @var (int|string)[]
     */
    private $idList;

    /**
     * @var array
     */
    private $searches;

    /**
     * @var string
     */
    private $resourceClass;

    /**
     * @var array
     */
    private $context;

    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    public function __construct(
        MockApiResourceDataLayer $dataLayer,
        array $idList,
        array $searches,
        string $resourceClass,
        array $context,
        PropertyAccessorInterface  $propertyAccessor
    ) {
        $this->dataLayer = $dataLayer;
        $this->idList = $idList;
        $this->searches = $searches;
        $this->resourceClass = $resourceClass;
        $this->context = $context;
        $this->propertyAccessor = $propertyAccessor;
    }

    public function getNbResults()
    {
        return count($this->idList);
    }

    public function getSlice($offset, $length)
    {
        $searchFilterRequest = new SearchFilterRequest(
            $offset,
            $length,
            $this->searches
        );

        return array_map(
            function ($id) {
                return $this->dataLayer->retrieve($this->resourceClass, $id, $this->context);
            },
            SearchFilterHelper::applySearchFilter($this->idList, $searchFilterRequest, $this->propertyAccessor)
        );
    }
}
