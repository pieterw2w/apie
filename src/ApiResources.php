<?php

namespace W2w\Lib\Apie;

/**
 * Container class that contains all the Api resource class names.
 */
class ApiResources
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
    public function getApiResources()
    {
        return $this->apiResources;
    }
}
