<?php
namespace W2w\Lib\Apie\Plugins\ValueObject\ValueObjects;

use erasys\OpenApi\Spec\v3\Schema;
use ReflectionClass;
use W2w\Lib\Apie\Exceptions\InvalidValueForValueObjectException;
use W2w\Lib\Apie\OpenApiSchema\Factories\SchemaFactory;

trait StringTrait
{
    private $value;

    final public static function fromNative($value)
    {
        return new self((string) $value);
    }

    final public function __construct(string $value)
    {
        if (!$this->validValue($value)) {
            throw new InvalidValueForValueObjectException($value, __CLASS__);
        }
        $this->value = $this->sanitizeValue($value);
    }

    abstract protected function validValue(string $value): bool;

    abstract protected function sanitizeValue(string $value): string;

    final public function toNative()
    {
        return $this->value;
    }

    final static public function toSchema(): Schema
    {
        $refl = new ReflectionClass(__CLASS__);
        return SchemaFactory::createStringSchema(
            strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $refl->getShortName()))
        );
    }
}
