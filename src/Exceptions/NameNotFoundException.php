<?php
namespace W2w\Lib\Apie\Exceptions;

class NameNotFoundException extends BadConfigurationException
{
    public function __construct(string $name)
    {
        parent::__construct('Name "' . $name . '" not found!"');
    }
}
