<?php

namespace W2w\Lib\Apie\Events;

/**
 * Event mediator for decoding a string to an array
 */
class DecodeEvent
{
    /**
     * @var object|null
     */
    private $resource;

    /**
     * @var string
     */
    private $acceptHeader;


    /**
     * @var string|int|array|null|float
     */
    private $decodedData;

    /**
     * @var bool
     */
    private $hasData = false;

    /**
     * @var string
     */
    private $requestBody;

    /**
     * @var string
     */
    private $resourceClass;

    /**
     * @param string $requestBody
     * @param string $acceptHeader
     * @param object|null $resource
     * @param string $resourceClass
     */
    public function __construct(string $requestBody, string $acceptHeader, ?object $resource, string $resourceClass)
    {
        $this->requestBody = $requestBody;
        $this->acceptHeader = $acceptHeader;
        $this->resource = $resource;
        $this->resourceClass = $resourceClass;
    }

    /**
     * @return string|int|array|null|float
     */
    public function getDecodedData()
    {
        return $this->decodedData;
    }

    /**
     * @param string|int|array|null|float $normalizedData
     */
    public function setDecodedData($normalizedData): void
    {
        $this->decodedData = $normalizedData;
        $this->hasData = true;
    }

    /**
     * If not null, it means an existing resource is being modified.
     *
     * @return object|null
     */
    public function getResource(): ?object
    {
        return $this->resource;
    }

    /**
     * @return string
     */
    public function getAcceptHeader(): string
    {
        return $this->acceptHeader;
    }

    /**
     * @return string
     */
    public function getRequestBody(): string
    {
        return $this->requestBody;
    }

    /**
     * @return bool
     */
    public function hasDecodedData(): bool
    {
        return $this->hasData;
    }

    /**
     * @return string
     */
    public function getResourceClass(): string
    {
        return $this->resourceClass;
    }
}
