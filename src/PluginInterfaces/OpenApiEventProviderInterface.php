<?php


namespace W2w\Lib\Apie\PluginInterfaces;

use erasys\OpenApi\Spec\v3\Document;

interface OpenApiEventProviderInterface
{
    public function onOpenApiDocGenerated(Document $document): Document;
}
