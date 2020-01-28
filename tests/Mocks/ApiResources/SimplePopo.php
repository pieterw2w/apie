<?php
namespace W2w\Test\Apie\Mocks\ApiResources;

use DateTime;
use W2w\Lib\Apie\Annotations\ApiResource;
use W2w\Lib\Apie\Plugins\Core\DataLayers\NullDataLayer;

/**
 * @ApiResource(persistClass=NullDataLayer::class)
 */
class SimplePopo
{
    private $id;

    private $createdAt;

    public $arbitraryField;

    public function __construct()
    {
        // the use of rand is deliberate so it's easier to test...
        $this->id = '';
        for ($i = 0; $i < 16; $i++) {
            $this->id .= chr(rand(ord('A'), ord('Z')));
        }

        $this->createdAt = new DateTime();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }
}
