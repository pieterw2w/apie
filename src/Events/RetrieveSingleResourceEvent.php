<?php

namespace W2w\Lib\Apie\Events;

use Psr\Http\Message\RequestInterface;
use W2w\Lib\Apie\Exceptions\InvalidReturnTypeOfApiResourceException;

/**
 * Event mediator for retrieving a specific resource with identifier.
 */
class RetrieveSingleResourceEvent
{
    /**
     * @var string
     */
    private $resourceClass;

    /**
     * @var string
     */
    private $id;

    /**
     * @var object|null
     */
    private $resource;

    /**
     * @var RequestInterface|null
     */
    private $request;

    /**
     * @param string $resourceClass
     * @param string $id
     * @param RequestInterface|null $request
     */
    public function __construct(string $resourceClass, string $id, ?RequestInterface $request)
    {
        $this->resourceClass = $resourceClass;
        $this->id = $id;
        $this->request = $request;
    }

    /**
     * @return string
     */
    public function getResourceClass(): string
    {
        return $this->resourceClass;
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
    public function setResource(object $resource)
    {
        if (!$resource instanceof $this->resourceClass) {
            throw new InvalidReturnTypeOfApiResourceException(null, get_class($resource), $this->resourceClass);
        }
        $this->resource = $resource;
    }

    /**
     * @return object|null
     */
    public function getResource(): ?object
    {
        return $this->resource;
    }

    /**
     * @return RequestInterface|null
     */
    public function getRequest(): ?RequestInterface
    {
        return $this->request;
    }
}
