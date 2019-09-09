<?php

namespace W2w\Lib\Apie;

use App\Models\ApiResources\ApiResourceFacadeResponse;
use W2w\Lib\Apie\Encoding\FormatRetriever;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ApiResourceFacade
{
    private $retriever;

    private $persister;

    private $converter;

    private $serializer;

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

    public function get(string $resourceClass, string $id, ?RequestInterface $request): ApiResourceFacadeResponse
    {
        $resource = $this->retriever->retrieve($resourceClass, $id);

        return $this->createResponse($resource, $request);
    }

    public function getAll(string $resourceClass, int $pageIndex, int $numberOfItems, ?RequestInterface $request): ApiResourceFacadeResponse
    {
        $resource = $this->retriever->retrieveAll($resourceClass, $pageIndex, $numberOfItems);

        return $this->createResponse($resource, $request);
    }

    public function put(string $resourceClass, string $id, RequestInterface $request): ApiResourceFacadeResponse
    {
        $resource = $this->get($resourceClass, $id, $request)->getResource();

        $contentFormat = $this->formatRetriever->getFormat($request->getHeader('Content-Type')[0] ?? 'application/json');
        $resource = $this->serializer->deserialize((string) $request->getBody(), $resourceClass, $contentFormat, ['groups' => ['base', 'write', 'put'], 'object_to_populate' => $resource]);

        $resource = $this->persister->persistExisting($resource, $id);

        return $this->createResponse($resource, $request);
    }

    public function post(string $resourceClass, RequestInterface $request): ApiResourceFacadeResponse
    {
        $contentFormat = $this->formatRetriever->getFormat($request->getHeader('Content-Type')[0] ?? 'application/json');
        $resource = $this->serializer->deserialize((string) $request->getBody(), $resourceClass, $contentFormat, ['groups' => ['base', 'write', 'post']]);
        $resource = $this->persister->persistNew($resource);

        return $this->createResponse($resource, $request);
    }

    private function createResponse($resource, ?RequestInterface $request): ApiResourceFacadeResponse
    {
        return new ApiResourceFacadeResponse(
            $this->serializer,
            ['groups' => ['base', 'read']],
            $resource,
            $this->formatRetriever,
            ($request && $request->hasHeader('Accept')) ? $request->getHeader('Accept')[0] : 'application/json'
        );
    }
}
