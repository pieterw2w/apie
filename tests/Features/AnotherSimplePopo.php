<?php
namespace W2w\Test\Apie\Features;

use DateTime;
use W2w\Lib\Apie\Annotations\ApiResource;
use W2w\Lib\Apie\Plugins\Core\DataLayers\MemoryDataLayer;

/**
 * @ApiResource(persistClass=MemoryDataLayer::class, retrieveClass=MemoryDataLayer::class)
 */
class AnotherSimplePopo
{
    private $id;

    private $createdAt;

    public $arbitraryField;

    public function __construct()
    {
        // the use of rand is deliberate so it's easier to test...
        $this->id = rand(0, 65525);

        $this->createdAt = new DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }
}
