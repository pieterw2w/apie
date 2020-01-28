<?php

namespace W2w\Lib\Apie\PluginInterfaces;

use erasys\OpenApi\Spec\v3\Info;

interface OpenApiInfoProviderInterface
{
    public function createInfo(): Info;
}
