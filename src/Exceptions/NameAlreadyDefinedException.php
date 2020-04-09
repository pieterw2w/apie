<?php

namespace W2w\Lib\Apie\Exceptions;

class NameAlreadyDefinedException extends BadConfigurationException
{
    public function __construct(string $name)
    {
        parent::__construct('Name "' . $name . '" is already defined!');
    }
}
