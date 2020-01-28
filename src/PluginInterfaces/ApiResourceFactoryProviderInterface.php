<?php

namespace W2w\Lib\Apie\PluginInterfaces;

use W2w\Lib\Apie\Interfaces\ApiResourceFactoryInterface;

interface ApiResourceFactoryProviderInterface
{
    public function getApiResourceFactory(): ApiResourceFactoryInterface;
}
