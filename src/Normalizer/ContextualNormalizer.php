<?php

namespace W2w\Lib\Apie\Normalizer;

use RuntimeException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ContextualNormalizer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface
{
    private static $globalDisabledNormalizers = [];

    private static $globalDisabledDenormalizers = [];

    private $normalizers;

    public function __construct(iterable $normalizers)
    {
        $this->normalizers = $normalizers;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        foreach ($this->normalizers as $normalizer) {
            if ($normalizer instanceof NormalizerInterface && $this->isNormalizerEnabled($normalizer, $context) && $normalizer->supportsNormalization($object, $format)) {
                return $normalizer->normalize($object, $format, $context);
            }
        }
        throw new RuntimeException('I can not normalize this object');
    }

    public function supportsNormalization($data, $format = null)
    {
        foreach ($this->normalizers as $normalizer) {
            if ($this->isNormalizerEnabled($normalizer, [])) {
                if ($normalizer->supportsNormalization($data, $format)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function denormalize($data, $class, $format = null, array $context = [])
    {
        foreach ($this->normalizers as $denormalizer) {
            if ($denormalizer instanceof DenormalizerInterface && $this->isDenormalizerEnabled($denormalizer, $context) && $denormalizer->supportsDenormalization($data, $class, $format)) {
                return $denormalizer->denormalize($data, $class, $format, $context);
            }
        }
        throw new RuntimeException('I can not normalize this object');
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        foreach ($this->normalizers as $normalizer) {
            if ($normalizer instanceof DenormalizerInterface && $this->isDenormalizerEnabled($normalizer, [])) {
                if ($normalizer->supportsDenormalization($data, $type, $format)) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function disableNormalizer(string $className)
    {
        self::$globalDisabledNormalizers[$className] = true;
    }

    public static function enableNormalizer(string $className)
    {
        unset(self::$globalDisabledNormalizers[$className]);
    }

    public static function disableDenormalizer(string $className)
    {
        self::$globalDisabledDenormalizers[$className] = true;
    }

    public static function enableDenormalizer(string $className)
    {
        unset(self::$globalDisabledDenormalizers[$className]);
    }

    private function isNormalizerEnabled(NormalizerInterface $normalizer, array $context): bool
    {
        $contextEnabled = $context['enabled_normalizers'] ?? [];
        $contextDisabled = $context['disabled_normalizers'] ?? [];
        $className = get_class($normalizer);
        if (in_array($className, $contextDisabled)) {
            return false;
        }
        if (in_array($className, $contextEnabled)) {
            return true;
        }

        return empty(self::$globalDisabledNormalizers[$className]);
    }

    private function isDenormalizerEnabled(DenormalizerInterface $normalizer, array $context): bool
    {
        $contextEnabled = $context['enabled_denormalizers'] ?? [];
        $contextDisabled = $context['disabled_denormalizers'] ?? [];
        $className = get_class($normalizer);
        if (in_array($className, $contextDisabled)) {
            return false;
        }
        if (in_array($className, $contextEnabled)) {
            return true;
        }

        return empty(self::$globalDisabledDenormalizers[$className]);
    }

    /**
     * Sets the owning Serializer object.
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        foreach ($this->normalizers as $normalizer) {
            if ($normalizer instanceof SerializerAwareInterface) {
                $normalizer->setSerializer($serializer);
            }
        }
    }
}
