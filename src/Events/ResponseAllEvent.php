<?php


namespace W2w\Lib\Apie\Events;


use W2w\Lib\Apie\Core\SearchFilters\SearchFilterRequest;

class ResponseAllEvent extends ResponseEvent
{
    /**
     * @var string
     */
    private $resourceClass;

    /**
     * @var SearchFilterRequest|null
     */
    private $searchFilterRequest;

    public function __construct(string $resourceClass, ?SearchFilterRequest  $searchFilterRequest, iterable $resource, string $acceptHeader)
    {
        $this->resourceClass = $resourceClass;
        $this->searchFilterRequest = $searchFilterRequest;
        parent::__construct($resource, $acceptHeader);
    }

    /**
     * Get the resource class.
     *
     * @return string
     */
    public function getResourceClass(): string
    {
        return $this->resourceClass;
    }

    /**
     * Get the search filter request.
     *
     * @return SearchFilterRequest|null
     */
    public function getSearchFilterRequest(): ?SearchFilterRequest
    {
        return $this->searchFilterRequest;
    }
}
