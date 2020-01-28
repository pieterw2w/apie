<?php


namespace W2w\Lib\Apie\Plugins\StaticConfig;


use W2w\Lib\Apie\PluginInterfaces\ApieConfigInterface;

class StaticConfigPlugin implements ApieConfigInterface
{
    private $baseUrl;

    public function __construct(string $baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }
}
