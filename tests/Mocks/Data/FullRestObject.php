<?php


namespace W2w\Test\Apie\Mocks\Data;

use Ramsey\Uuid\Uuid;
use W2w\Lib\Apie\Annotations\ApiResource;
use W2w\Lib\Apie\Retrievers\ArrayPersister;

/**
 * @ApiResource(persistClass=ArrayPersister::class, retrieveClass=ArrayPersister::class)
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

    public function __construct(?Uuid $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }
}
