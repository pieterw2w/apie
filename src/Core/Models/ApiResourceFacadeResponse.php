<?php

namespace W2w\Lib\Apie\Core\Models;

use Psr\Http\Message\ResponseInterface;
use W2w\Lib\Apie\Interfaces\ResourceSerializerInterface;

/**
 * Data class returned by ApiResourceFacade.
 */
class ApiResourceFacadeResponse
{
    private $serializer;

    private $resource;

    private $acceptHeader;

    /**
     * @param ResourceSerializerInterface $serializer
     * @param mixed $resource
     * @param string|null $acceptHeader
     */
    public function __construct(
        ResourceSerializerInterface $serializer,
        $resource,
        ?string $acceptHeader
    ) {
        $this->serializer = $serializer;
        $this->resource = $resource;
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
        return $this->serializer->toResponse($this->resource, $this->acceptHeader ?? 'application/json');
    }

    /**
     * Gets data the way we would send it normalized.
     *
     * @return mixed
     */
    public function getNormalizedData()
    {
        return $this->serializer->normalize($this->resource, $this->acceptHeader ?? 'application/json');
    }
}
