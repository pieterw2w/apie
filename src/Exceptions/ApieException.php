<?php


namespace W2w\Lib\Apie\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Base class that is extended by all Apie classes.
 * @deprecated use \W2w\Lib\ApieObjectAccessNormalizer\Exceptions\ApieException
 */
abstract class ApieException extends \W2w\Lib\ApieObjectAccessNormalizer\Exceptions\ApieException
{
}
