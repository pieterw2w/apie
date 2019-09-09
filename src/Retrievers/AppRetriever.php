<?php

namespace W2w\Lib\Apie\Retrievers;

use Symfony\Component\HttpKernel\Exception\HttpException;
use W2w\Lib\Apie\ApiResources\App;

/**
 * Retrieves instances of api resource App. This is always one record with id 'name'.
 */
class AppRetriever implements ApiResourceRetrieverInterface
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
     * @return App
     */
    public function retrieve(string $resourceClass, $id, array $context)
    {
        if ($id !== 'name') {
            throw new HttpException(404, 'Identifier should be "name"');
        }

        return new App(
            $this->appName,
            $this->environment,
            $this->hash,
            $this->debug
        );
    }

    /**
     * @param string $resourceClass
     * @param array $context
     * @param int $pageIndex
     * @param int $numberOfItems
     * @return App[]
     */
    public function retrieveAll(string $resourceClass, array $context, int $pageIndex, int $numberOfItems): iterable
    {
        if ($pageIndex > 0) {
            return [];
        }

        return [$this->retrieve($resourceClass, 'name', $context)];
    }
}
