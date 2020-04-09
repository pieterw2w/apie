<?php
namespace W2w\Lib\Apie\Exceptions;

use W2w\Lib\ApieObjectAccessNormalizer\Exceptions\ApieException;

class CanNotDetermineIdException extends ApieException
{
    public function __construct($resource, string $identifier = 'id')
    {
        parent::__construct(500, 'Resource ' . get_class($resource) . ' has no ' . $identifier . ' property');
    }
}
