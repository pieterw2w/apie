<?php


namespace W2w\Lib\Apie\Interfaces;

use W2w\Lib\Apie\PluginInterfaces\SubActionsProviderInterface;

/**
 * Interface that objects used in SubActionsProviderInterface if the class-mapping
 * can not be determing statically.
 *
 * @see SubActionsProviderInterface
 */
interface SupportedAwareSubActionInterface
{
    public function isSupported(string $resourceClass): bool;
}
