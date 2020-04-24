<?php

namespace W2w\Lib\Apie\Events;

use Psr\Http\Message\ResponseInterface;

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
}
