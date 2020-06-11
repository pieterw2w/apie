<?php


namespace W2w\Lib\Apie\PluginInterfaces;

use W2w\Lib\Apie\Interfaces\SupportedAwareSubActionInterface;

/**
 * Interface for returning a list of sub actions. These sub actions add POST actions to resources
 * by appending a path.
 *
 * @see SupportedAwareSubActionInterface
 */
interface SubActionsProviderInterface
{
    /**
     * Return sub actions. The key is the name of the path. The action should implement SupportedAwareSubActionInterface
     * or has a handle method with at least one argument and a return type(which can not be typehinted in php properly).
     *
     * @return object[][]
     */
    public function getSubActions();
}
