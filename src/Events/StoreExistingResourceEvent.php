<?php


namespace W2w\Lib\Apie\Events;


use W2w\Lib\Apie\Exceptions\InvalidReturnTypeOfApiResourceException;

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
     * @param StoreExistingResourceEvent|StoreNewResourceEvent $event
     */
    public function __construct($event)
    {
        $this->event = $event;
        $this->setResource($event->getResource());
    }

    /**
     * @return ModifySingleResourceEvent
     */
    public function getEvent(): ModifySingleResourceEvent
    {
        return $this->event;
    }

    /**
     * @return object
     */
    public function getResource(): object
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
