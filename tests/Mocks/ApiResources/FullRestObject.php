<?php
namespace W2w\Test\Apie\Mocks\ApiResources;

use Ramsey\Uuid\Uuid;
use W2w\Lib\Apie\Annotations\ApiResource;
use W2w\Lib\Apie\Plugins\Core\DataLayers\MemoryDataLayer;
use W2w\Test\Apie\OpenApiSchema\ValueObject;

/**
 * @ApiResource(
 *     persistClass=MemoryDataLayer::class,
 *     retrieveClass=MemoryDataLayer::class,
 *     context={
 *         "search": {
 *             "uuid": "string",
 *             "stringValue": "string",
 *             "valueObject": "string"
 *         }
 *     }
 * )
 */
class FullRestObject
{
    /**
     * @var Uuid
     */
    private $uuid;

    /**
     * @var string
     */
    public $stringValue;

    /**
     * @var ValueObject
     */
    public $valueObject;

    public function __construct(?Uuid $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }
}
