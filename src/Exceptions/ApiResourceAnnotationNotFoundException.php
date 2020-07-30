<?php
namespace W2w\Lib\Apie\Exceptions;

use W2w\Lib\ApieObjectAccessNormalizer\Exceptions\ApieException;

/**
 * Exception thrown if no ApiResource annotation is found on the class docblock.
 */
class ApiResourceAnnotationNotFoundException extends ApieException
{
    public function __construct($classNameOrInstance)
    {
        $className = gettype($classNameOrInstance) === 'object' ? get_class($classNameOrInstance) : $classNameOrInstance;
        parent::__construct(500, 'Class ' . $className . ' has no ApiResource annotation.');
    }
}
