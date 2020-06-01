<?php


namespace W2w\Test\Apie\OpenApiSchema\Data;

use W2w\Lib\Apie\Annotations\ApiResource;
use W2w\Lib\Apie\Plugins\Core\DataLayers\NullDataLayer;

/**
 * @ApiResource(persistClass=NullDataLayer::class, retrieveClass=NullDataLayer::class))
 */
class RecursiveObject
{
    /**
     * @var RecursiveObject
     */
    private $child;

    public function setChild(?RecursiveObject $child): self
    {
        $this->child = $child;
        return $this;
    }

    public function getChild(): ?RecursiveObject
    {
        return $this->child;
    }
}
