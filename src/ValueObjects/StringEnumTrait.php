<?php
namespace W2w\Lib\Apie\ValueObjects;

use erasys\OpenApi\Spec\v3\Schema;
use ReflectionClass;

trait StringEnumTrait
{
    use StringTrait { toSchema as private internalToSchema; }

    final protected function validValue(string $value): bool
    {
        $values = self::getValidValues();
        return isset($values[$value]) || false !== array_search($value, $values, true);
    }

    final protected function sanitizeValue(string $value): string
    {
        $values = self::getValidValues();
        if (isset($values[$value])) {
            return $values[$value];
        }
        return $value;
    }


    final public static function getValidValues()
    {
        $reflectionClass = new ReflectionClass(__CLASS__);
        return $reflectionClass->getConstants();
    }

    final static public function toSchema(): Schema
    {
        $schema = self::internalToSchema();
        $schema->enum = array_values(self::getValidValues());
        return $schema;
    }

}
