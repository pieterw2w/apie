<?php


namespace W2w\Lib\Apie\Exceptions;

class CouldNotRemoveFileException extends ApieException
{
    public function __construct(string $filename)
    {
        parent::__construct(503, 'Could not remove file "' . $filename . '""');
    }
}
