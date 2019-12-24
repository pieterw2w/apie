<?php

namespace W2w\Lib\Apie\Exceptions;

class ValidationException extends ApieException
{
    private $errors;

    public function __construct(array $errors, \Exception $previous = null)
    {
        $this->errors = $errors;
        parent::__construct(422, 'A validation error occurred', $previous);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
