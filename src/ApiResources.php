<?php

namespace W2w\Lib\Apie;

class ApiResources
{
    private $apiResources;

    public function __construct(array $apiResources)
    {
        $this->apiResources = $apiResources;
    }

    public function getApiResources()
    {
        return $this->apiResources;
    }
}
