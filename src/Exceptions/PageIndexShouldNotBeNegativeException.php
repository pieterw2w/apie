<?php
namespace W2w\Lib\Apie\Exceptions;

/**
 * Exception thrown when the page index filled in for pagination is negative.
 */
class PageIndexShouldNotBeNegativeException extends ApieException
{
    public function __construct()
    {
        parent::__construct(422, 'Page index should not be negative!');
    }
}
