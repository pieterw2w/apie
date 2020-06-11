<?php

namespace W2w\Lib\Apie\Core;

use Generator;
use Throwable;
use W2w\Lib\Apie\Apie;
use W2w\Lib\Apie\Exceptions\BadConfigurationException;
use W2w\Lib\Apie\Exceptions\NotAnApiePluginException;

/**
 * Helper method to get all plugins with certain properties.
 *
 * @internal
 */
final class PluginContainer
{
    private $plugins;

    private $mapping = [];

    public function __construct(array $plugins)
    {
        foreach ($plugins as $plugin) {
            if (!is_object($plugin)) {
                throw new NotAnApiePluginException($plugin);
            }
            $interfaces = class_implements($plugin);
            foreach ($interfaces as $interface) {
                if (strpos($interface, 'W2w\Lib\Apie\PluginInterfaces') === 0) {
                    continue(2);
                }
            }
            throw new NotAnApiePluginException($plugin);
        }
        $this->plugins = $plugins;
    }

    /**
     * Returns all plugins with a specific interface.
     *
     * @param string $interface
     * @return array
     */
    public function getPluginsWithInterface(string $interface): array
    {
        if (!isset($this->mapping[$interface])) {
            $res = [];
            foreach ($this->plugins as $plugin) {
                if ($plugin instanceof $interface) {
                    $res[] = $plugin;
                }
            }
            $this->mapping[$interface] = $res;
        }
        return $this->mapping[$interface];
    }

    /**
     * Returns plugin instance.
     *
     * @param string $pluginClass
     * @return object
     */
    public function getPlugin(string $pluginClass): object
    {
        $last = null;
        foreach ($this->plugins as $plugin) {
            if ($plugin instanceof $pluginClass) {
                return $plugin;
            }
            $last = $plugin;
        }
        if ($last instanceof Apie) {
            return $last->getPlugin($pluginClass);
        }
        throw new BadConfigurationException('Plugin ' . $pluginClass . ' not found!');
    }

    /**
     * Returns first plugin or throw error.
     *
     * @param string $interface
     * @param Throwable $error
     * @return object
     */
    public function first(string $interface, ?Throwable $error): ?object
    {
        $plugins = $this->getPluginsWithInterface($interface);
        if (empty($plugins)) {
            if ($error) {
                throw $error;
            }
            return null;
        }
        return reset($plugins);
    }

    /**
     * Iterate over all plugins with a specific interface with a callback.
     *
     * @param string $interface
     * @param callable $callback
     * @return array
     */
    public function each(string $interface, callable $callback)
    {
        return array_map($callback, $this->getPluginsWithInterface($interface));
    }

    /**
     * Merge the results of a getter in a list of plugins into a single array.
     *
     * @param string $interface
     * @param string $getterMethod
     * @return array
     */
    public function merge(string $interface, string $getterMethod): array
    {
        $res = [];
        foreach ($this->getPluginsWithInterface($interface) as $plugin) {
            $res = array_merge($res, $plugin->$getterMethod());
        }
        return $res;
    }

    /**
     * Iterate over a getter of a list of plugins and return all return values in a generator.
     *
     * @param string $interface
     * @param string $getterMethod
     * @return Generator
     */
    public function combine(string $interface, string $getterMethod): Generator
    {
        foreach ($this->getPluginsWithInterface($interface) as $plugin) {
            yield $plugin->$getterMethod();
        }
    }
}
