<?php
namespace W2w\Lib\Apie\Core\Resources;

/**
 * Interface for classes that just return a list of class names to be used as Api Resource.
 */
interface ApiResourcesInterface
{
    /**
     * @return string[]
     */
    public function getApiResources(): array;
}
