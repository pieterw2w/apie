<?php

namespace W2w\Lib\Apie\Normalizer;

use PhpValueObjects\AbstractStringValueObject;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class StringValueObjectNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        return new $class($data);
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return is_a($type, AbstractStringValueObject::class, true);
    }

    public function normalize($object, $format = null, array $context = [])
    {
        return (string) $object;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AbstractStringValueObject;
    }
}
