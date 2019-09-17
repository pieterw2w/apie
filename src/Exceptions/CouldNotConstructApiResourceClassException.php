<?php
namespace W2w\Lib\Apie\Exceptions;

use Throwable;

/**
 * Thrown by ApiResourceFactory to tell it can not instantiate a class.
 */
class CouldNotConstructApiResourceClassException extends ApieException
{
    public function __construct($identifier, Throwable $previous = null)
    {
        $message = 'Class '
            . $identifier
            . ' has required constructor arguments and need to be registered as a service in a service provider.';
        parent::__construct(500, $message, $previous);
    }
}
