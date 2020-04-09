<?php

namespace W2w\Lib\Apie\Exceptions;

use W2w\Lib\ApieObjectAccessNormalizer\Exceptions\ApieException;

/**
 * Exception thrown when a wrong HTTP method is chosen.
 */
class MethodNotAllowedException extends ApieException
{
    public function __construct(string $method)
    {
        parent::__construct(405, "Resource has no " . $method . " support");
    }
}
