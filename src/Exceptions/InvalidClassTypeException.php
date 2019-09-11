<?php


namespace W2w\Lib\Apie\Exceptions;

/**
 * Exception thrown when a class is expected of a certain interface.
 */
class InvalidClassTypeException extends ApieException
{
    public function __construct(string $identifier, string $expectedInterface)
    {
        $message = $identifier . ' does not implement ' . $expectedInterface;
        parent::__construct(500, $message);
    }
}
