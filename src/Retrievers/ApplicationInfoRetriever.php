<?php

namespace W2w\Lib\Apie\Retrievers;

use W2w\Lib\Apie\ApiResources\ApplicationInfo;
use W2w\Lib\Apie\Exceptions\ResourceNotFoundException;
use W2w\Lib\Apie\SearchFilters\SearchFilterRequest;

/**
 * Retrieves instances of api resource ApplicationInfo. This is always one record with id 'name'.
 */
class ApplicationInfoRetriever implements ApiResourceRetrieverInterface
{
    /**
     * @var string
     */
    private $appName;

    /**
     * @var string
     */
    private $environment;

    /**
     * @var string
     */
    private $hash;

    /**
     * @var bool
     */
    private $debug;

    /**
     * @param string $appName
     * @param string $environment
     * @param string $hash
     * @param bool $debug
     */
    public function __construct(string $appName, string $environment, string $hash, bool $debug)
    {
        $this->appName = $appName;
        $this->environment = $environment;
        $this->hash = $hash;
        $this->debug = $debug;
    }

    /**
     * @param string $resourceClass
     * @param string $id
     * @param array $context
     * @return ApplicationInfo
     */
    public function retrieve(string $resourceClass, $id, array $context)
    {
        if ($id !== 'name') {
            throw new ResourceNotFoundException($id);
        }

        return new ApplicationInfo(
            $this->appName,
            $this->environment,
            $this->hash,
            $this->debug
        );
    }

    /**
     * @param string $resourceClass
     * @param array $context
     * @param SearchFilterRequest $searchFilterRequest
     * @return ApplicationInfo[]
     */
    public function retrieveAll(
        string $resourceClass,
        array $context,
        SearchFilterRequest $searchFilterRequest
    ): iterable {
        if ($searchFilterRequest->getPageIndex() > 0) {
            return [];
        }

        return [$this->retrieve($resourceClass, 'name', $context)];
    }
}
