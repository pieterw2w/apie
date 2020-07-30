<?php

namespace W2w\Lib\Apie\Exceptions;

class NotAnApiePluginException extends BadConfigurationException
{
    public function __construct(object $plugin)
    {
        parent::__construct('Object of class "' . get_class($plugin) . '" is not a valid Apie plugin!');
    }
}
