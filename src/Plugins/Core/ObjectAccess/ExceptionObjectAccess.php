<?php


namespace W2w\Lib\Apie\Plugins\Core\ObjectAccess;

use ReflectionClass;
use W2w\Lib\Apie\Plugins\Core\ObjectAccess\Getters\CodeGetter;
use W2w\Lib\Apie\Plugins\Core\ObjectAccess\Getters\ErrorsGetter;
use W2w\Lib\Apie\Plugins\Core\ObjectAccess\Getters\MessageGetter;
use W2w\Lib\ApieObjectAccessNormalizer\Exceptions\ValidationException;
use W2w\Lib\ApieObjectAccessNormalizer\ObjectAccess\ObjectAccess;

class ExceptionObjectAccess extends ObjectAccess
{
    /**
     * @var bool
     */
    private $debug;

    public function __construct(bool $debug)
    {
        $this->debug = $debug;
        parent::__construct(true, true);
    }

    /**
     * Excepions are always immutable.
     *
     * @param ReflectionClass $reflectionClass
     * @return array
     */
    protected function getSetterMapping(ReflectionClass $reflectionClass): array
    {
        return [];
    }

    protected function getGetterMapping(ReflectionClass $reflectionClass): array
    {
        $mapping = parent::getGetterMapping($reflectionClass);
        $mapping['message'] = [new MessageGetter()];
        $mapping['code'] = [new CodeGetter()];
        unset($mapping['file']);
        unset($mapping['line']);
        unset($mapping['previous']);
        if ($this->debug) {
            $mapping['trace'] = $mapping['traceAsString'];
        } else {
            unset($mapping['trace']);
        }
        unset($mapping['traceAsString']);
        if ($reflectionClass->name === ValidationException::class) {
            $mapping['errors'] = [new ErrorsGetter()];
            unset($mapping['exceptions']);
            unset($mapping['i18n']);
            unset($mapping['errorBag']);
            unset($mapping['headers']);
            unset($mapping['statusCode']);
        }
        return $mapping;
    }
}
