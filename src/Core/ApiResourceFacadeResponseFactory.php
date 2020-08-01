<?php

namespace W2w\Lib\Apie\Core;

use Psr\Http\Message\RequestInterface;
use W2w\Lib\Apie\Core\Models\ApiResourceFacadeResponse;
use W2w\Lib\Apie\Core\Models\ApiResourceListFacadeResponse;
use W2w\Lib\Apie\Core\SearchFilters\SearchFilterRequest;
use W2w\Lib\Apie\Interfaces\ResourceSerializerInterface;
use W2w\Lib\Apie\PluginInterfaces\ResourceLifeCycleInterface;

class ApiResourceFacadeResponseFactory
{
    /**
     * @var ResourceSerializerInterface
     */
    private $serializer;

    /**
     * @var ResourceLifeCycleInterface[]
     */
    private $resourceLifeCycles;

    public function __construct(
        ResourceSerializerInterface $serializer,
        iterable $resourceLifeCycles
    ) {
        $this->serializer = $serializer;
        $this->resourceLifeCycles = $resourceLifeCycles;
    }

    public function createResponseForResource($resource, ?RequestInterface $request): ApiResourceFacadeResponse
    {
        return new ApiResourceFacadeResponse(
            $this->serializer,
            $resource,
            ($request && $request->hasHeader('Accept')) ? $request->getHeader('Accept')[0] : 'application/json',
            $this->resourceLifeCycles
        );
    }

    public function createResponseListForResource($resource, string $resourceClass, SearchFilterRequest $searchFilter, ?RequestInterface $request): ApiResourceListFacadeResponse
    {
        return new ApiResourceListFacadeResponse(
            $this->serializer,
            $resource,
            $resourceClass,
            $searchFilter,
            ($request && $request->hasHeader('Accept')) ? $request->getHeader('Accept')[0] : 'application/json',
            $this->resourceLifeCycles
        );
    }
}
