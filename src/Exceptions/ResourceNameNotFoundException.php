<?php
namespace W2w\Lib\Apie\Exceptions;

use W2w\Lib\ApieObjectAccessNormalizer\Exceptions\ApieException;

/**
 * Exception thrown when a resource name is not found.
 */
class ResourceNameNotFoundException extends ApieException
{
    public function __construct(string $resourceName, string $extraText = '')
    {
        parent::__construct(404, '"' . $resourceName . '" resource not found!' . $extraText);
    }
}
