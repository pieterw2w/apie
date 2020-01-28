<?php


namespace W2w\Lib\Apie\PluginInterfaces;

use W2w\Lib\Apie\Interfaces\ResourceSerializerInterface;

interface SerializerProviderInterface
{
    public function getResourceSerializer(): ResourceSerializerInterface;
}
