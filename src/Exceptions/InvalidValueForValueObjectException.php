<?php
namespace W2w\Lib\Apie\Exceptions;

use ReflectionClass;

class InvalidValueForValueObjectException extends ApieException
{
    public function __construct($value, $valueObject)
    {
        $refl = new ReflectionClass($valueObject);
        $name = strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $refl->getShortName()));
        parent::__construct(
            422,
            '"' . $value . '" is not a valid value for value object ' . $name
        );
    }
}
