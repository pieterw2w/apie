<?php
namespace W2w\Lib\Apie\Exceptions;

class BadConfigurationException extends ApieException
{
    public function __construct(
        string $message
    ) {
        parent::__construct(500, $message);
    }
}
