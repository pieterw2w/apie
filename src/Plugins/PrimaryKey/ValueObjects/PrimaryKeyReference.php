<?php


namespace W2w\Lib\Apie\Plugins\PrimaryKey\ValueObjects;


use W2w\Lib\Apie\Core\Models\ApiResourceClassMetadata;
use W2w\Lib\Apie\Interfaces\ValueObjectInterface;

/**
 * Value object for making a primary key reference to an Apie resource.
 */
final class PrimaryKeyReference
{
    /**
     * @var ApiResourceClassMetadata
     */
    private $metadata;

    /**
     * @var string
     */
    private $identifierValue;

    /**
     * @var string
     */
    private $resourceUrl;

    /**
     * @param ApiResourceClassMetadata $metadata
     * @param string|int|float|bool|object $identifierValue
     */
    public function __construct(ApiResourceClassMetadata $metadata, string $resourceUrl, $identifierValue)
    {
        $this->metadata = $metadata;
        $this->resourceUrl = $resourceUrl;
        if ($identifierValue instanceof ValueObjectInterface) {
            $this->identifierValue = (string) $identifierValue->toNative();
        } else {
            $this->identifierValue = (string) $identifierValue;
        }
    }

    /**
     * @return ApiResourceClassMetadata
     */
    public function getMetadata(): ApiResourceClassMetadata
    {
        return $this->metadata;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->resourceUrl;
    }

    /**
     * @return string
     */
    public function getIdentifierValue(): string
    {
        return $this->identifierValue;
    }
}
