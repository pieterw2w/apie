<?php
namespace W2w\Lib\Apie\Exceptions;

/**
 * Exception thrown when a resource is not found.
 */
class ResourceNotFoundException extends ApieException
{
    public function __construct(string $resourceName)
    {
        parent::__construct(404, '"' . $resourceName . '" resource not found!');
    }
}
