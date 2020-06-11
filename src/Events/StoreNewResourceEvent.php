<?php


namespace W2w\Lib\Apie\Events;


use Psr\Http\Message\RequestInterface;
use W2w\Lib\Apie\Exceptions\InvalidReturnTypeOfApiResourceException;

/**
 * Event mediator for adding a new resource to a data layer,
 */
class StoreNewResourceEvent
{
    /**
     * @var string
     */
    private $resourceClass;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var object|null
     */
    private $resource;

    public function __construct(string $resourceClass, RequestInterface $request)
    {
        $this->resourceClass = $resourceClass;
        $this->request = $request;
    }

    /**
     * @param RequestInterface $request
     */
    public function setRequest(RequestInterface $request): void
    {
        $this->request = $request;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * @return object|null
     */
    public function getResource(): ?object
    {
        return $this->resource;
    }

    /**
     * @param object|null $resource
     */
    public function setResource(?object $resource): void
    {
        if (!$resource instanceof $this->resourceClass) {
            throw new InvalidReturnTypeOfApiResourceException(null, get_class($resource), $this->resourceClass);
        }
        $this->resource = $resource;
    }
}
