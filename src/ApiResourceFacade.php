<?php
namespace W2w\Lib\Apie;

use Psr\Http\Message\RequestInterface;
use Symfony\Component\Serializer\SerializerInterface;
use W2w\Lib\Apie\Encodings\FormatRetriever;
use W2w\Lib\Apie\Models\ApiResourceFacadeResponse;

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
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var FormatRetriever
     */
    private $formatRetriever;

    public function __construct(
        ApiResourceRetriever $retriever,
        ApiResourcePersister $persister,
        ClassResourceConverter $converter,
        SerializerInterface $serializer,
        FormatRetriever $formatRetriever
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
            ['groups' => ['base', 'delete']],
            null,
            $this->formatRetriever,
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
     * @param int $pageIndex
     * @param int $numberOfItems
     * @param RequestInterface|null $request
     * @return ApiResourceFacadeResponse
     */
    public function getAll(string $resourceClass, int $pageIndex, int $numberOfItems, ?RequestInterface $request): ApiResourceFacadeResponse
    {
        $resource = $this->retriever->retrieveAll($resourceClass, $pageIndex, $numberOfItems);

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

        $contentFormat = $this->formatRetriever->getFormat(
            $request->getHeader('Content-Type')[0] ?? 'application/json'
        );
        $resource = $this->serializer->deserialize(
            (string) $request->getBody(),
            $resourceClass,
            $contentFormat,
            ['groups' => ['base', 'write', 'put'], 'object_to_populate' => $resource]
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
        $contentFormat = $this->formatRetriever->getFormat(
            $request->getHeader('Content-Type')[0] ?? 'application/json'
        );
        $resource = $this->serializer->deserialize(
            (string) $request->getBody(),
            $resourceClass,
            $contentFormat,
            ['groups' => ['base', 'write', 'post']]
        );
        $resource = $this->persister->persistNew($resource);

        return $this->createResponse($resource, $request);
    }

    /**
     * Creates a ApiResourceFacadeResponse instance.
     *
     * @param $resource
     * @param RequestInterface|null $request
     * @return ApiResourceFacadeResponse
     */
    private function createResponse($resource, ?RequestInterface $request): ApiResourceFacadeResponse
    {
        return new ApiResourceFacadeResponse(
            $this->serializer,
            ['groups' => ['base', 'read', 'get']],
            $resource,
            $this->formatRetriever,
            ($request && $request->hasHeader('Accept')) ? $request->getHeader('Accept')[0] : 'application/json'
        );
    }
}
