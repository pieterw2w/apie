<?php

namespace W2w\Lib\Apie\Models;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
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
     * @param mixed $resource
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
        $format = $this->formatRetriever->getFormat($this->acceptHeader ?? 'application/json');
        $contentType = $this->formatRetriever->getContentType($format);
        $response = $this->serializer->serialize($this->resource, $format, $this->serializerContext);

        return new TextResponse($response, is_null($this->resource) ? 204 : 200, ['content-type' => $contentType]);
    }

    /**
     * Gets data the way we would send it normalized.
     *
     * @return mixed
     */
    public function getNormalizedData()
    {
        if (!$this->serializer instanceof NormalizerInterface) {
            throw new RuntimeException('ApiResourceFacadeResponse requires a serializer with Normalizer support for this method');
        }
        $format = $this->formatRetriever->getFormat($this->acceptHeader ?? 'application/json');
        return $this->serializer->normalize($this->resource, $format, $this->serializerContext);
    }
}
