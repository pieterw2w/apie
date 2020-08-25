<?php


namespace W2w\Lib\Apie\PluginInterfaces;

use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

interface SymfonyComponentProviderInterface
{
    public function getClassMetadataFactory(): ClassMetadataFactoryInterface;
    public function getPropertyConverter(): NameConverterInterface;
}
