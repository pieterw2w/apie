<?php


namespace W2w\Lib\Apie\PluginInterfaces;

use Symfony\Component\PropertyInfo\PropertyAccessExtractorInterface;
use Symfony\Component\PropertyInfo\PropertyDescriptionExtractorInterface;
use Symfony\Component\PropertyInfo\PropertyInitializableExtractorInterface;
use Symfony\Component\PropertyInfo\PropertyListExtractorInterface;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;

/**
 * @deprecated use ObjectAccessProviderInterface instead
 */
interface PropertyInfoExtractorProviderInterface
{
    /**
     * @return PropertyListExtractorInterface[]
     */
    public function getListExtractors(): array;

    /**
     * @return PropertyTypeExtractorInterface[]
     */
    public function getTypeExtractors(): array;

    /**
     * @return PropertyDescriptionExtractorInterface[]
     */
    public function getDescriptionExtractors(): array;

    /**
     * @return PropertyAccessExtractorInterface[]
     */
    public function getAccessExtractors(): array;

    /**
     * @return PropertyInitializableExtractorInterface[]
     */
    public function getInitializableExtractors(): array;
}
