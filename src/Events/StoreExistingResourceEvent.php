<?php


namespace W2w\Lib\Apie\Events;


use Psr\Http\Message\RequestInterface;
use W2w\Lib\Apie\Exceptions\InvalidReturnTypeOfApiResourceException;

/**
 * Event mediator for storing a resource to the data layer.
 */
class StoreExistingResourceEvent
{
    /**
     * @var ModifySingleResourceEvent
     */
    private $event;

    /**
     * @var object|null
     */
    private $resource;

    /**
     * @param ModifySingleResourceEvent|StoreNewResourceEvent $event
     */
    public function __construct($event)
    {
        $this->event = $event;
        $this->setResource($event->getResource());
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        if ($this->event instanceof  ModifySingleResourceEvent) {
            return $this->event->getId();
        }
        return null;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->event->getRequest();
    }

    /**
     * @return ModifySingleResourceEvent|StoreNewResourceEvent
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return object|null
     */
    public function getResource(): ?object
    {
        return $this->resource;
    }

    /**
     * @param object $resource
     */
    public function setResource(object $resource): void
    {
        $className = get_class($this->event->getResource());
        if (!$resource instanceof $className) {
            throw new InvalidReturnTypeOfApiResourceException(null, get_class($resource), $className);
        }
        $this->resource = $resource;
    }
}
