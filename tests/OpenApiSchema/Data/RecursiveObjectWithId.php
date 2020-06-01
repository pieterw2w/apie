<?php


namespace W2w\Test\Apie\OpenApiSchema\Data;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use W2w\Lib\Apie\Annotations\ApiResource;
use W2w\Lib\Apie\Plugins\Core\DataLayers\NullDataLayer;

/**
 * @ApiResource(persistClass=NullDataLayer::class, retrieveClass=NullDataLayer::class))
 */
class RecursiveObjectWithId
{
    private $id;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    /**
     * @var RecursiveObjectWithId
     */
    private $child;

    public function setChild(?RecursiveObjectWithId $child): self
    {
        $this->child = $child;
        return $this;
    }

    public function getChild(): ?RecursiveObjectWithId
    {
        return $this->child;
    }
}
