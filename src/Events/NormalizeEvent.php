<?php

namespace W2w\Lib\Apie\Events;

class NormalizeEvent
{
    private $resource;

    /**
     * @var string
     */
    private $acceptHeader;

    private $hasNormalizedData = false;

    private $normalizedData;

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
     * @return bool
     */
    public function hasNormalizedData(): bool
    {
        return $this->hasNormalizedData;
    }

    /**
     * @return mixed
     */
    public function getNormalizedData()
    {
        return $this->normalizedData;
    }

    /**
     * @param mixed $normalizedData
     */
    public function setNormalizedData($normalizedData): void
    {
        $this->normalizedData = $normalizedData;
        $this->hasNormalizedData = true;
    }
}
