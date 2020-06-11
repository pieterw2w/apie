<?php


namespace W2w\Lib\Apie\PluginInterfaces;

use W2w\Lib\Apie\Events\DecodeEvent;
use W2w\Lib\Apie\Events\DeleteResourceEvent;
use W2w\Lib\Apie\Events\ModifySingleResourceEvent;
use W2w\Lib\Apie\Events\NormalizeEvent;
use W2w\Lib\Apie\Events\ResponseEvent;
use W2w\Lib\Apie\Events\RetrievePaginatedResourcesEvent;
use W2w\Lib\Apie\Events\RetrieveSingleResourceEvent;
use W2w\Lib\Apie\Events\StoreExistingResourceEvent;
use W2w\Lib\Apie\Events\StoreNewResourceEvent;

interface ResourceLifeCycleInterface
{
    /**
     * Run before a resource is deleted.
     *
     * @param DeleteResourceEvent $event
     */
    public function onPreDeleteResource(DeleteResourceEvent $event);

    /**
     * Run after a resource is deleted.
     *
     * @param DeleteResourceEvent $event
     */
    public function onPostDeleteResource(DeleteResourceEvent $event);

    /**
     * Run before a resource is retrieved.
     *
     * @param RetrieveSingleResourceEvent $event
     */
    public function onPreRetrieveResource(RetrieveSingleResourceEvent $event);

    /**
     * Run after a resource is retrieved.
     *
     * @param RetrieveSingleResourceEvent $event
     */
    public function onPostRetrieveResource(RetrieveSingleResourceEvent $event);

    /**
     * Run before a list of resources is retrieved.
     *
     * @param RetrievePaginatedResourcesEvent $event
     */
    public function onPreRetrieveAllResources(RetrievePaginatedResourcesEvent $event);

    /**
     * Run after a list of resources is retrieved.
     *
     * @param RetrievePaginatedResourcesEvent $event
     */
    public function onPostRetrieveAllResources(RetrievePaginatedResourcesEvent $event);

    /**
     * Run before an existing resource is being persisted.
     *
     * @param ModifySingleResourceEvent $event
     */
    public function onPrePersistExistingResource(StoreExistingResourceEvent $event);

    /**
     * Run after an existing resource has been persisted.
     *
     * @param ModifySingleResourceEvent $event
     */
    public function onPostPersistExistingResource(StoreExistingResourceEvent $event);

    /**
     * Run before a request body is being decoded.
     *
     * @param DecodeEvent $event
     */
    public function onPreDecodeRequestBody(DecodeEvent $event);

    /**
     * Run before a request body is being decoded.
     *
     * @param DecodeEvent $event
     */
    public function onPostDecodeRequestBody(DecodeEvent $event);

    /**
     * Run before an existing resource is being modified.
     * @param ModifySingleResourceEvent $event
     */
    public function onPreModifyResource(ModifySingleResourceEvent $event);

    /**
     * Run after an existing resource is being modified.
     * @param ModifySingleResourceEvent $event
     */
    public function onPostModifyResource(ModifySingleResourceEvent $event);

    /**
     * Run before a new resource is added.
     * @param ModifySingleResourceEvent $event
     */
    public function onPreCreateResource(StoreNewResourceEvent $event);

    /**
     * Run after a new resource is added.
     * @param ModifySingleResourceEvent $event
     */
    public function onPostCreateResource(StoreNewResourceEvent $event);

    /**
     * Run before a modified existing resource has been persisted.
     *
     * @param StoreExistingResourceEvent $event
     */
    public function onPrePersistNewResource(StoreExistingResourceEvent $event);

    /**
     * Run after a modified existing resource has been persisted.
     *
     * @param StoreExistingResourceEvent $event
     */
    public function onPostPersistNewResource(StoreExistingResourceEvent $event);

    /**
     * Run before a response is created.
     *
     * @param ResponseEvent $event
     */
    public function onPreCreateResponse(ResponseEvent $event);

    /**
     * Run after a response is created.
     *
     * @param ResponseEvent $event
     */
    public function onPostCreateResponse(ResponseEvent $event);

    /**
     * Run before normalized data is created.
     *
     * @param NormalizeEvent $event
     */
    public function onPreCreateNormalizedData(NormalizeEvent $event);

    /**
     * Run after normalized data is created.
     *
     * @param NormalizeEvent $event
     */
    public function onPostCreateNormalizedData(NormalizeEvent $event);
}
