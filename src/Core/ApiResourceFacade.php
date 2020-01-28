<?php
namespace W2w\Lib\Apie\Core;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use W2w\Lib\Apie\Core\Models\ApiResourceFacadeResponse;
use W2w\Lib\Apie\Core\SearchFilters\SearchFilterRequest;
use W2w\Lib\Apie\Interfaces\FormatRetrieverInterface;
use W2w\Lib\Apie\Interfaces\ResourceSerializerInterface;

class ApiResourceFacade
{
    /**
     * @var ApiResourceRetriever
     */
    private $retriever;

    /**
     * @var ApiResourcePersister
     */
    private $persister;

    /**
     * @var ClassResourceConverter
     */
    private $converter;

    /**
     * @var ResourceSerializerInterface
     */
    private $serializer;

    /**
     * @var FormatRetrieverInterface
     */
    private $formatRetriever;

    public function __construct(
        ApiResourceRetriever $retriever,
        ApiResourcePersister $persister,
        ClassResourceConverter $converter,
        ResourceSerializerInterface $serializer,
        FormatRetrieverInterface $formatRetriever
    ) {
        $this->retriever = $retriever;
        $this->persister = $persister;
        $this->converter = $converter;
        $this->serializer = $serializer;
        $this->formatRetriever = $formatRetriever;
    }

    /**
     * Does a DELETE instance call.
     *
     * @param string $resourceClass
     * @param string $id
     * @return ApiResourceFacadeResponse
     */
    public function delete(string $resourceClass, string $id): ApiResourceFacadeResponse
    {
        $this->persister->delete($resourceClass, $id);

        return new ApiResourceFacadeResponse(
            $this->serializer,
            null,
            'application/json'
        );
    }

    /**
     * Does a GET instance call.
     *
     * @param string $resourceClass
     * @param string $id
     * @param RequestInterface|null $request
     * @return ApiResourceFacadeResponse
     */
    public function get(string $resourceClass, string $id, ?RequestInterface $request): ApiResourceFacadeResponse
    {
        $resource = $this->retriever->retrieve($resourceClass, $id);

        return $this->createResponse($resource, $request);
    }

    /**
     * Does a GET all call.
     *
     * @param string $resourceClass
     * @param ServerRequestInterface|null $request
     * @return ApiResourceFacadeResponse
     */
    public function getAll(string $resourceClass, ?ServerRequestInterface $request): ApiResourceFacadeResponse
    {
        $searchFilterRequest = new SearchFilterRequest();
        if ($request) {
            $searchFilterRequest = SearchFilterRequest::createFromPsrRequest($request);
        }
        $resource = $this->retriever->retrieveAll($resourceClass, $searchFilterRequest);

        return $this->createResponse($resource, $request);
    }

    /**
     * Does a PUT instance call.
     *
     * @param string $resourceClass
     * @param string $id
     * @param RequestInterface $request
     * @return ApiResourceFacadeResponse
     */
    public function put(string $resourceClass, string $id, RequestInterface $request): ApiResourceFacadeResponse
    {
        $resource = $this->get($resourceClass, $id, $request)->getResource();

        $resource = $this->serializer->putData(
            $resource,
            (string) $request->getBody(),
            $request->getHeader('Content-Type')[0] ?? 'application/json'
        );

        $resource = $this->persister->persistExisting($resource, $id);

        return $this->createResponse($resource, $request);
    }

    /**
     * Does a POST new instance call.
     *
     * @param string $resourceClass
     * @param RequestInterface $request
     * @return ApiResourceFacadeResponse
     */
    public function post(string $resourceClass, RequestInterface $request): ApiResourceFacadeResponse
    {
        $resource = $this->serializer->postData(
            $resourceClass,
            (string) $request->getBody(),
            $request->getHeader('Content-Type')[0] ?? 'application/json'
        );
        $resource = $this->persister->persistNew($resource);

        return $this->createResponse($resource, $request);
    }

    /**
     * Creates a ApiResourceFacadeResponse instance.
     *
     * @param mixed $resource
     * @param RequestInterface|null $request
     * @return ApiResourceFacadeResponse
     */
    private function createResponse($resource, ?RequestInterface $request): ApiResourceFacadeResponse
    {
        return new ApiResourceFacadeResponse(
            $this->serializer,
            $resource,
            ($request && $request->hasHeader('Accept')) ? $request->getHeader('Accept')[0] : 'application/json'
        );
    }
}
