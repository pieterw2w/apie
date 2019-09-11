<?php
namespace W2w\Lib\Apie\Exceptions;

/**
 * Thrown by ApiResourceFactory to tell it can not instantiate a class.
 */
class CouldNotConstructApiResourceClassException extends ApieException
{
    public function __construct($identifier)
    {
        $message = 'Class '
            . $identifier
            . ' has required constructor arguments and need to be registered as a service in a service provider.';
        parent::__construct(500, $message);
    }
}
