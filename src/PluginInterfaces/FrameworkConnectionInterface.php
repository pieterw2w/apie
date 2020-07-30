<?php


namespace W2w\Lib\Apie\PluginInterfaces;


use W2w\Lib\Apie\Core\SearchFilters\SearchFilterRequest;

/**
 * Interface to connect Apie with a framework.
 */
interface FrameworkConnectionInterface
{
    /**
     * Gets/creates service from a service container.
     *
     * @param string $id
     * @return object
     */
    public function getService(string $id): object;

    /**
     * Returns url for a single resource or null if the url could not be generated.
     *
     * @param object $resource
     * @return string|null
     */
    public function getUrlForResource(object $resource): ?string;

    /**
     * Returns url for a resource or null if the url could not be generated for a find all resources GET request.
     *
     * @param string $resourceClass
     * @param SearchFilterRequest|null $filterRequest
     * @return string|null
     */
    public function getOverviewUrlForResourceClass(string $resourceClass, ?SearchFilterRequest $filterRequest = null): ?string;

    /**
     * Returns an example url for a resource.
     *
     * @param string $resourceClass
     * @return string|null
     */
    public function getExampleUrl(string $resourceClass): ?string;

    /**
     * Returns the language that will be accepted.
     *
     * @return string|null
     */
    public function getAcceptLanguage(): ?string;

    /**
     * Returns the language of the object that will be denormalized into an object.
     *
     * @return string|null
     */
    public function getContentLanguage(): ?string;
}
