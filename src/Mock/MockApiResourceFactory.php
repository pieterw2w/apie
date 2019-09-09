<?php

namespace W2w\Lib\Apie\Mock;

use W2w\Lib\Apie\ApiResourceFactory;
use W2w\Lib\Apie\ApiResourceFactoryInterface;
use W2w\Lib\Apie\Persister\ApiResourcePersisterInterface;
use W2w\Lib\Apie\Retriever\ApiResourceRetrieverInterface;

class MockApiResourceFactory implements ApiResourceFactoryInterface
{
    private $retriever;

    private $factory;

    private $skippedResources;

    public function __construct(MockApiResourceRetriever $retriever, ApiResourceFactory $factory, array $skippedResources)
    {
        $this->retriever = $retriever;
        $this->factory = $factory;
        $this->skippedResources = $skippedResources;
    }

    public function getApiResourceRetrieverInstance(string $identifier): ApiResourceRetrieverInterface
    {
        $retriever = $this->factory->getApiResourceRetrieverInstance($identifier);
        if (!in_array(get_class($retriever), $this->skippedResources)) {
            return $this->retriever;
        }

        return $retriever;
    }

    public function getApiResourcePersisterInstance(string $identifier): ApiResourcePersisterInterface
    {
        $persister = $this->factory->getApiResourcePersisterInstance($identifier);
        if (!in_array(get_class($persister), $this->skippedResources)) {
            return $this->retriever;
        }

        return $persister;
    }
}
