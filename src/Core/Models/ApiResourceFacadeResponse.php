<?php

namespace W2w\Lib\Apie\Core\Models;

use Psr\Http\Message\ResponseInterface;
use W2w\Lib\Apie\Events\NormalizeEvent;
use W2w\Lib\Apie\Events\ResponseEvent;
use W2w\Lib\Apie\Interfaces\ResourceSerializerInterface;
use W2w\Lib\Apie\PluginInterfaces\ResourceLifeCycleInterface;

/**
 * Data class returned by ApiResourceFacade.
 */
class ApiResourceFacadeResponse
{
    private $serializer;

    private $resource;

    private $acceptHeader;

    /**
     * @var iterable<ResourceLifeCycleInterface>
     */
    private $resourceLifeCycles;

    /**
     * @param ResourceSerializerInterface $serializer
     * @param mixed $resource
     * @param string|null $acceptHeader
     */
    public function __construct(
        ResourceSerializerInterface $serializer,
        $resource,
        ?string $acceptHeader,
        iterable $resourceLifeCycles = []
    ) {
        $this->serializer = $serializer;
        $this->resource = $resource;
        $this->acceptHeader = $acceptHeader;
        $this->resourceLifeCycles = $resourceLifeCycles;
    }

    /**
     * Helper method to call the method on all all lifecycle instances.
     *
     * @param string $event
     * @param mixed[] $args
     */
    private function runLifeCycleEvent(string $event, ...$args)
    {
        foreach ($this->resourceLifeCycles as $resourceLifeCycle) {
            $resourceLifeCycle->$event(...$args);
        }
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
        $event = new ResponseEvent($this->resource, $this->acceptHeader ?? 'application/json');
        $this->runLifeCycleEvent('onPreCreateResponse', $event);
        if (!$event->getResponse()) {
            $event->setResponse($this->serializer->toResponse($this->resource, $this->acceptHeader ?? 'application/json'));
        }
        $this->runLifeCycleEvent('onPostCreateResponse', $event);

        return $event->getResponse();
    }

    /**
     * Gets data the way we would send it normalized.
     *
     * @return mixed
     */
    public function getNormalizedData()
    {
        $event = new NormalizeEvent($this->resource, $this->acceptHeader ?? 'application/json');
        $this->runLifeCycleEvent('onPreCreateNormalizedData', $event);
        if (!$event->hasNormalizedData()) {
            $event->setNormalizedData($this->serializer->normalize($this->resource, $this->acceptHeader ?? 'application/json'));
        }
        $this->runLifeCycleEvent('onPostCreateNormalizedData', $event);
        return $event->getNormalizedData();
    }
}
