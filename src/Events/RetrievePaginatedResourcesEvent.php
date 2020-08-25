<?php

namespace W2w\Lib\Apie\Events;

use Pagerfanta\Pagerfanta;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use W2w\Lib\Apie\Core\SearchFilters\SearchFilterRequest;
use W2w\Lib\Apie\Exceptions\InvalidReturnTypeOfApiResourceException;

/**
 * Event mediator for retrieving a list of resources.
 */
class RetrievePaginatedResourcesEvent
{
    /**
     * @var string
     */
    private $resourceClass;

    /**
     * @var SearchFilterRequest
     */
    private $searchFilterRequest;

    /**
     * @var object[]|null
     */
    private $resources;

    /**
     * @var RequestInterface|null
     */
    private $request;

    /**
     * @param string $resourceClass
     * @param SearchFilterRequest $searchFilterRequest
     * @param ServerRequestInterface|null $request
     */
    public function __construct(string $resourceClass, SearchFilterRequest $searchFilterRequest, ?ServerRequestInterface $request)
    {
        $this->resourceClass = $resourceClass;
        $this->searchFilterRequest = $searchFilterRequest;
        $this->request = $request;
    }

    /**
     * @return string
     */
    public function getResourceClass(): string
    {
        return $this->resourceClass;
    }

    /**
     * @return SearchFilterRequest
     */
    public function getSearchFilterRequest(): SearchFilterRequest
    {
        return $this->searchFilterRequest;
    }

    /**
     * @param object[] $resources
     */
    public function setResources(iterable $resources)
    {
        $resourceArray = [];
        foreach ($resources as $resource) {
            if (!$resource instanceof $this->resourceClass) {
                throw new InvalidReturnTypeOfApiResourceException(null, get_class($resource), $this->resourceClass);
            }
            $resourceArray[] = $resource;
        }
        $this->resources = is_object($resources) ? $resources : $resourceArray;
    }

    /**
     * @return object[]|Pagerfanta|null
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * @return RequestInterface|null
     */
    public function getRequest(): ?RequestInterface
    {
        return $this->request;
    }
}
