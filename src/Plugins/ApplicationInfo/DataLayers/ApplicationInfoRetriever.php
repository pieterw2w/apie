<?php

namespace W2w\Lib\Apie\Plugins\ApplicationInfo\DataLayers;

use W2w\Lib\Apie\Core\SearchFilters\SearchFilterRequest;
use W2w\Lib\Apie\Interfaces\ApiResourceRetrieverInterface;
use W2w\Lib\Apie\Plugins\ApplicationInfo\ApiResources\ApplicationInfo;

/**
 * Retrieves instances of api resource ApplicationInfo. This is always one record.
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
        return [
            $this->retrieve($resourceClass, '', $context)
        ];
    }
}
