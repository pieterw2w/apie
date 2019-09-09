<?php

namespace W2w\Lib\Apie\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Throwable;

class ExceptionNormalizer implements NormalizerInterface
{
    private $showStack;

    public function __construct(bool $showStack)
    {
        $this->showStack = $showStack;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $res = [
            'type'    => (new \ReflectionClass($object))->getShortName(),
            'message' => $object->getMessage(),
            'code'    => $object->getCode(),
        ];
        if ($this->showStack) {
            $res['trace'] = $object->getTraceAsString();
        }

        return $res;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Throwable;
    }
}
