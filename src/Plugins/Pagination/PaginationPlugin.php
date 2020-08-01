<?php

namespace W2w\Lib\Apie\Plugins\Pagination;

use erasys\OpenApi\Spec\v3\Document;
use erasys\OpenApi\Spec\v3\Header;
use erasys\OpenApi\Spec\v3\Operation;
use erasys\OpenApi\Spec\v3\PathItem;
use erasys\OpenApi\Spec\v3\Reference;
use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use W2w\Lib\Apie\Core\SearchFilters\SearchFilterHelper;
use W2w\Lib\Apie\Events\DecodeEvent;
use W2w\Lib\Apie\Events\DeleteResourceEvent;
use W2w\Lib\Apie\Events\ModifySingleResourceEvent;
use W2w\Lib\Apie\Events\NormalizeEvent;
use W2w\Lib\Apie\Events\ResponseAllEvent;
use W2w\Lib\Apie\Events\ResponseEvent;
use W2w\Lib\Apie\Events\RetrievePaginatedResourcesEvent;
use W2w\Lib\Apie\Events\RetrieveSingleResourceEvent;
use W2w\Lib\Apie\Events\StoreExistingResourceEvent;
use W2w\Lib\Apie\Events\StoreNewResourceEvent;
use W2w\Lib\Apie\PluginInterfaces\ApieAwareInterface;
use W2w\Lib\Apie\PluginInterfaces\ApieAwareTrait;
use W2w\Lib\Apie\PluginInterfaces\NormalizerProviderInterface;
use W2w\Lib\Apie\PluginInterfaces\OpenApiEventProviderInterface;
use W2w\Lib\Apie\PluginInterfaces\ResourceLifeCycleInterface;
use W2w\Lib\Apie\Plugins\Pagination\Normalizers\PaginatorNormalizer;

class PaginationPlugin implements ResourceLifeCycleInterface, NormalizerProviderInterface, OpenApiEventProviderInterface, ApieAwareInterface
{
    use ApieAwareTrait;

    const PREV_HEADER = 'x-pagination-previous';

    const NEXT_HEADER = 'x-pagination-next';

    const FIRST_HEADER = 'x-pagination-first';

    const LAST_HEADER = 'x-pagination-last';

    const COUNT_HEADER = 'x-pagination-count';

    public function getNormalizers(): array
    {
        return [new PaginatorNormalizer()];
    }

    public function onOpenApiDocGenerated(Document $document): Document
    {
        /** @var PathItem[] $paths */
        $paths = $document->paths ?? [];
        foreach ($paths as $url => $path) {
            if (strpos($url, '{id}', 0) === false && $path->get) {
                $this->patch($path->get);
            }
        }
        return $document;
    }

    private function patch(Operation $operation): Operation
    {
        foreach ($operation->responses as &$response) {
            if ($response instanceof Reference) {
                continue;
            }
            $response->headers[self::PREV_HEADER] = new Header('Pagination previous page url', []);
            $response->headers[self::NEXT_HEADER] = new Header('Pagination next page url', []);
            $response->headers[self::FIRST_HEADER] = new Header('Pagination first page url', []);
            $response->headers[self::LAST_HEADER] = new Header('Pagination last page url', []);
        }
        return $operation;
    }

    public function onPreDeleteResource(DeleteResourceEvent $event)
    {
    }

    public function onPostDeleteResource(DeleteResourceEvent $event)
    {
    }

    public function onPreRetrieveResource(RetrieveSingleResourceEvent $event)
    {
    }

    public function onPostRetrieveResource(RetrieveSingleResourceEvent $event)
    {
    }

    public function onPreRetrieveAllResources(RetrievePaginatedResourcesEvent $event)
    {
    }

    public function onPostRetrieveAllResources(RetrievePaginatedResourcesEvent $event)
    {
    }

    public function onPrePersistExistingResource(StoreExistingResourceEvent $event)
    {
    }

    public function onPostPersistExistingResource(StoreExistingResourceEvent $event)
    {
    }

    public function onPreDecodeRequestBody(DecodeEvent $event)
    {
    }

    public function onPostDecodeRequestBody(DecodeEvent $event)
    {
    }

    public function onPreModifyResource(ModifySingleResourceEvent $event)
    {
    }

    public function onPostModifyResource(ModifySingleResourceEvent $event)
    {
    }

    public function onPreCreateResource(StoreNewResourceEvent $event)
    {
    }

    public function onPostCreateResource(StoreNewResourceEvent $event)
    {
    }

    public function onPrePersistNewResource(StoreExistingResourceEvent $event)
    {
    }

    public function onPostPersistNewResource(StoreExistingResourceEvent $event)
    {
    }

    public function onPreCreateResponse(ResponseEvent $event)
    {
    }

    public function onPostCreateResponse(ResponseEvent $event)
    {
        if (!($event instanceof ResponseAllEvent)) {
            return;
        }
        $resource = $event->getResource();
        if (!($resource instanceof Pagerfanta)) {
            if (is_array($resource)) {
                $resource = new Pagerfanta(new ArrayAdapter($resource));
            } else if (is_iterable($resource)) {
                $resource = new Pagerfanta(new ArrayAdapter(iterator_to_array($resource)));
            } else {
                return;
            }
            $event->getSearchFilterRequest()->updatePaginator($resource);
        }
        $response = $event->getResponse()
            ->withHeader(self::FIRST_HEADER, $this->generateUrl($event, 0))
            ->withHeader(self::LAST_HEADER, $this->generateUrl($event, $resource->getNbPages() - 1))
            ->withHeader(self::COUNT_HEADER, $this->generateUrl($event, $resource->getNbPages()));
        if ($resource->hasPreviousPage()) {
            $response = $response->withHeader(self::PREV_HEADER, $this->generateUrl($event, $resource->getPreviousPage()));
        }
        if ($resource->hasNextPage()) {
            $response = $response->withHeader(self::NEXT_HEADER, $this->generateUrl($event, $resource->getNextPage()));
        }
        $event->setResponse($response);
    }

    private function generateUrl(ResponseAllEvent  $event, int $page)
    {
        $baseUrl = $this->getApie()->getOverviewUrlForResourceClass($event->getResourceClass(), $event->getSearchFilterRequest());
        return $baseUrl . '?' . http_build_query(['page' => $page, 'limit' => $event->getSearchFilterRequest()->getNumberOfItems()]);
    }

    public function onPreCreateNormalizedData(NormalizeEvent $event)
    {
    }

    public function onPostCreateNormalizedData(NormalizeEvent $event)
    {
    }
}
