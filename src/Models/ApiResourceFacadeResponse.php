<?php

namespace W2w\Lib\Apie\Models;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Serializer\SerializerInterface;
use W2w\Lib\Apie\Encodings\FormatRetriever;
use Zend\Diactoros\Response\TextResponse;

/**
 * Data class returned by ApiResourceFacade.
 */
class ApiResourceFacadeResponse
{
    private $serializer;

    private $serializerContext;

    private $resource;

    private $formatRetriever;

    private $acceptHeader;

    /**
     * @param SerializerInterface $serializer
     * @param array $serializerContext
     * @param $resource
     * @param FormatRetriever $formatRetriever
     * @param string|null $acceptHeader
     */
    public function __construct(
        SerializerInterface $serializer,
        array $serializerContext,
        $resource,
        FormatRetriever $formatRetriever,
        ?string $acceptHeader
    ) {
        $this->serializer = $serializer;
        $this->serializerContext = $serializerContext;
        $this->resource = $resource;
        $this->formatRetriever = $formatRetriever;
        $this->acceptHeader = $acceptHeader;
    }

    /**
     * @return mixed
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        $format = $this->formatRetriever->getFormat($this->acceptHeader);
        $contentType = $this->formatRetriever->getContentType($format);
        var_dump($this->serializerContext);
        $response = $this->serializer->serialize($this->resource, $format, $this->serializerContext);

        return new TextResponse($response, is_null($this->resource) ? 204 : 200, ['content-type' => $contentType]);
    }
}
