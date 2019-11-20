<?php
namespace W2w\Lib\Apie\Exceptions;

class InvalidIdException extends ApieException
{
    public function __construct(string $id)
    {
        parent::__construct(500, 'Id "' . $id . '" is not valid as identifier');
    }
}
