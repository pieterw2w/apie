<?php


namespace W2w\Lib\Apie\Events;

use Psr\Http\Message\RequestInterface;

/**
 * Event mediator for the event that an api resource will be modified.
 */
class ModifySingleResourceEvent
{
    /**
     * @var object
     */
    private $resource;

    /**
     * @var string
     */
    private $id;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(object $resource, string $id, RequestInterface $request)
    {
        $this->resource = $resource;
        $this->id = $id;
        $this->request = $request;
    }

    /**
     * @return object
     */
    public function getResource(): object
    {
        return $this->resource;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * @param RequestInterface $request
     */
    public function setRequest(RequestInterface $request): void
    {
        $this->request = $request;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param object $resource
     */
    public function setResource(object $resource): void
    {
        $this->resource = $resource;
    }
}
