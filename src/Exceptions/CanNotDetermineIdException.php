<?php
namespace W2w\Lib\Apie\Exceptions;

class CanNotDetermineIdException extends ApieException
{
    public function __construct($resource, string $identifier = 'id')
    {
        parent::__construct(500, 'Resource ' . get_class($resource) . ' has no ' . $identifier . ' property');
    }
}
