<?php

namespace W2w\Lib\Apie\Events;

use Psr\Http\Message\ResponseInterface;

/**
 * Event mediator for normalizing a value from a hydrated resource to a PSR response.
 */
class ResponseEvent
{
    private $resource;

    /**
     * @var string
     */
    private $acceptHeader;

    /**
     * @var ResponseInterface|null
     */
    private $response;

    /**
     * @param mixed $resource
     * @param string $acceptHeader
     */
    public function __construct($resource, string $acceptHeader)
    {
        $this->resource = $resource;
        $this->acceptHeader = $acceptHeader;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    /**
     * @param ResponseInterface $response
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * Get the accept header.
     *
     * @return string
     */
    public function getAcceptHeader(): string
    {
        return $this->acceptHeader;
    }

    /**
     * Get the resource.
     *
     * @return mixed
     */
    public function getResource()
    {
        return $this->resource;
    }
}
