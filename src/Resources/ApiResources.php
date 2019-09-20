<?php

namespace W2w\Lib\Apie\Resources;

/**
 * Container class that contains all the Api resource class names statically.
 */
class ApiResources implements ApiResourcesInterface
{
    /**
     * @var string[]
     */
    private $apiResources;

    /**
     * @param string[] $apiResources
     */
    public function __construct(array $apiResources)
    {
        $this->apiResources = $apiResources;
    }

    /**
     * @return string[]
     */
    public function getApiResources(): array
    {
        return $this->apiResources;
    }
}
