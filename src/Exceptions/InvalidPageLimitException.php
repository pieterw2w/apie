<?php


namespace W2w\Lib\Apie\Exceptions;

/**
 * Exception thrown when a page limit for the pagination is not a valid value.
 */
class InvalidPageLimitException extends ApieException
{
    public function __construct()
    {
        parent::__construct(422, 'Page limit should not be lower than 1!');
    }
}
