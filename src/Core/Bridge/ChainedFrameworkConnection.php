<?php


namespace W2w\Lib\Apie\Core\Bridge;

use W2w\Lib\Apie\Core\SearchFilters\SearchFilterRequest;
use W2w\Lib\Apie\PluginInterfaces\FrameworkConnectionInterface;

class ChainedFrameworkConnection implements FrameworkConnectionInterface
{
    /**
     * @var FrameworkConnectionInterface[]
     */
    private $connections;

    /**
     * @param FrameworkConnectionInterface[] $connections
     * @param FrameworkConnectionInterface $default
     */
    public function __construct(array $connections, FrameworkConnectionInterface $default)
    {
        $this->connections = $connections;
        $this->connections[] = $default;
    }

    public function getService(string $id): object
    {
        return reset($this->connections)->getService($id);
    }

    public function getUrlForResource(object $resource): ?string
    {
        foreach ($this->connections as $connection) {
            $url = $connection->getUrlForResource($resource);
            if (null !== $url) {
                return $url;
            }
        }
        return null;
    }

    public function getExampleUrl(string $resourceClass): ?string
    {
        foreach ($this->connections as $connection) {
            $url = $connection->getExampleUrl($resourceClass);
            if (null !== $url) {
                return $url;
            }
        }
        return null;
    }

    public function getOverviewUrlForResourceClass(string $resourceClass, ?SearchFilterRequest $filterRequest = null
    ): ?string {
        foreach ($this->connections as $connection) {
            $url = $connection->getOverviewUrlForResourceClass($resourceClass, $filterRequest);
            if (null !== $url) {
                return $url;
            }
        }
        return null;
    }

    public function getAcceptLanguage(): ?string
    {
        foreach ($this->connections as $connection) {
            $language = $connection->getAcceptLanguage();
            if (null !== $language) {
                return $language;
            }
        }
        return null;
    }

    public function getContentLanguage(): ?string
    {
        foreach ($this->connections as $connection) {
            $language = $connection->getContentLanguage();
            if (null !== $language) {
                return $language;
            }
        }
        return null;
    }
}
