<?php


namespace W2w\Lib\Apie\Exceptions;

use W2w\Lib\ApieObjectAccessNormalizer\Exceptions\ApieException;

class CouldNotWriteFileException extends ApieException
{
    public function __construct(string $filename)
    {
        parent::__construct(503, 'Could not write file "' . $filename . '""');
    }
}
