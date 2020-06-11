<?php

namespace W2w\Lib\Apie\Events;

/**
 * Event mediator for normalizing a value from a hydrated resource to an array,
 */
class NormalizeEvent
{
    /**
     * @var object|iterable
     */
    private $resource;

    /**
     * @var string
     */
    private $acceptHeader;

    /**
     * This variable is added, because the normalized data could be null(DELETE for example) and still being normalized.
     *
     * @var bool
     */
    private $hasNormalizedData = false;

    /**
     * @var string|int|array|null|float
     */
    private $normalizedData;

    /**
     * @param object|iterable $resource
     * @param string $acceptHeader
     */
    public function __construct($resource, string $acceptHeader)
    {
        $this->resource = $resource;
        $this->acceptHeader = $acceptHeader;
    }

    /**
     * Returns true if the normalized data was filled in.
     *
     * @return bool
     */
    public function hasNormalizedData(): bool
    {
        return $this->hasNormalizedData;
    }

    /**
     * @return string|int|array|null|float
     */
    public function getNormalizedData()
    {
        return $this->normalizedData;
    }

    /**
     * @param string|int|array|null|float $normalizedData
     */
    public function setNormalizedData($normalizedData): void
    {
        $this->normalizedData = $normalizedData;
        $this->hasNormalizedData = true;
    }
}
