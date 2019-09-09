<?php

namespace W2w\Lib\Apie\Mocks;

use W2w\Lib\Apie\ApiResourceFactory;
use W2w\Lib\Apie\ApiResourceFactoryInterface;
use W2w\Lib\Apie\Persisters\ApiResourcePersisterInterface;
use W2w\Lib\Apie\Retrievers\ApiResourceRetrieverInterface;

/**
 * ApiResourceFactoryInterface implementation of a mock REST server. We require the original ApiResourceFactory to
 * find out which methods are allowed (GET, POST, PUT etc.).
 *
 * @TODO: add some hook to have some control over the mocking...
 */
class MockApiResourceFactory implements ApiResourceFactoryInterface
{
    /**
     * @var MockApiResourceRetriever
     */
    private $retriever;

    /**
     * @var ApiResourceFactory
     */
    private $factory;

    /**
     * @var string[]
     */
    private $skippedResources;

    /**
     * @param MockApiResourceRetriever $retriever
     * @param ApiResourceFactory $factory
     * @param string[] $skippedResources resources that are not allowed to be mocked.
     */
    public function __construct(
        MockApiResourceRetriever $retriever,
        ApiResourceFactory $factory,
        array $skippedResources
    ) {
        $this->retriever = $retriever;
        $this->factory = $factory;
        $this->skippedResources = $skippedResources;
    }

    /**
     * Returns a class that retrieves an API resource.
     *
     * @param string $identifier
     * @return ApiResourceRetrieverInterface
     */
    public function getApiResourceRetrieverInstance(string $identifier): ApiResourceRetrieverInterface
    {
        $retriever = $this->factory->getApiResourceRetrieverInstance($identifier);
        if (!in_array(get_class($retriever), $this->skippedResources)) {
            return $this->retriever;
        }

        return $retriever;
    }

    /**
     * Returns a class or null that persists API resources.
     *
     * @param string $identifier
     * @return ApiResourcePersisterInterface
     */
    public function getApiResourcePersisterInstance(string $identifier): ApiResourcePersisterInterface
    {
        $persister = $this->factory->getApiResourcePersisterInstance($identifier);
        if (!in_array(get_class($persister), $this->skippedResources)) {
            return $this->retriever;
        }

        return $persister;
    }
}
