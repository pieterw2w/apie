<?php

namespace W2w\Lib\Apie\Normalizers;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use ReflectionClass;
use Throwable;

/**
 * Class that normalizes a throwable class.
 */
class ExceptionNormalizer implements NormalizerInterface
{
    private $showStack;

    /**
     * @param bool $showStack If true, outputs a stack trace.
     */
    public function __construct(bool $showStack)
    {
        $this->showStack = $showStack;
    }

    /**
     * @param Throwable $object
     * @param string|null $format
     * @param array $context
     * @return string[]
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $res = [
            'type'    => (new ReflectionClass($object))->getShortName(),
            'message' => $object->getMessage(),
            'code'    => $object->getCode(),
        ];
        if ($this->showStack) {
            $res['trace'] = $object->getTraceAsString();
        }

        return $res;
    }

    /**
     * @param mixed $data
     * @param string|null $format
     * @return bool
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Throwable;
    }
}
