<?php


namespace W2w\Lib\Apie\Core\Bridge;

use W2w\Lib\Apie\Apie;
use W2w\Lib\Apie\Core\SearchFilters\SearchFilterRequest;
use W2w\Lib\Apie\Exceptions\BadConfigurationException;
use W2w\Lib\Apie\PluginInterfaces\ApieAwareInterface;
use W2w\Lib\Apie\PluginInterfaces\ApieAwareTrait;
use W2w\Lib\Apie\PluginInterfaces\FrameworkConnectionInterface;

class FrameworkLessConnection implements FrameworkConnectionInterface, ApieAwareInterface
{
    use ApieAwareTrait;

    public function __construct(Apie $apie)
    {
        $this->setApie($apie);
    }

    public function getService(string $id): object
    {
        throw new BadConfigurationException('No service ' . $id . ' found!');
    }

    public function getUrlForResource(object $resource): ?string
    {
        $classResourceConverter = $this->getApie()->getClassResourceConverter();
        $identifierExtractor = $this->getApie()->getIdentifierExtractor();
        $apiMetadataFactory = $this->getApie()->getApiResourceMetadataFactory();
        $metadata = $apiMetadataFactory->getMetadata($resource);
        $identifier = $identifierExtractor->getIdentifierValue($resource, $metadata->getContext());
        if (!$identifier || !$metadata->allowGet()) {
            return null;
        }
        return $this->getBaseUrl() . '/' . $classResourceConverter->normalize($metadata->getClassName()) . '/' . $identifier;
    }

    public function getExampleUrl(string $resourceClass): ?string
    {
        $url = $this->getOverviewUrlForResourceClass($resourceClass);
        if (null === $url) {
            return null;
        }
        return $url . '/12345';
    }

    public function getOverviewUrlForResourceClass(string $resourceClass, ?SearchFilterRequest $filterRequest = null
    ): ?string {
        $classResourceConverter = $this->getApie()->getClassResourceConverter();
        $apiMetadataFactory = $this->getApie()->getApiResourceMetadataFactory();
        $metadata = $apiMetadataFactory->getMetadata($resourceClass);
        if (!$metadata->allowGetAll()) {
            return null;
        }
        $query = '';
        if ($filterRequest) {
            $searchQuery = $filterRequest->getSearches();
            $searchQuery['page'] = $filterRequest->getPageIndex();
            $searchQuery['limit'] = $filterRequest->getNumberOfItems();
            $query = '?' . http_build_query($searchQuery);
        }
        return $this->getBaseUrl() . '/' . $classResourceConverter->normalize($metadata->getClassName()) . $query;
    }

    /**
     * Returns base url if one is set up.
     *
     * @return string
     */
    private function getBaseUrl(): string
    {
        try {
            return $this->getApie()->getBaseUrl();
        } catch (BadConfigurationException $exception) {
            return '';
        }
    }

    public function getAcceptLanguage(): ?string
    {
        return null;
    }

    public function getContentLanguage(): ?string
    {
        return null;
    }
}
