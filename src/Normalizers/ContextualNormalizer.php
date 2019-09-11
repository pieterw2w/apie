<?php

namespace W2w\Lib\Apie\Normalizers;

use Symfony\Component\Serializer\Exception\UnsupportedException;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Normalizer for the symfony serializer to enable/disable a normalizer by context. This can be done globally with
 * ContextualNormalizer::enableNormalizer and ContextualNormalizer::disableNormalizer or by providing it in the
 * context of the serializer.
 */
class ContextualNormalizer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    /**
     * @var string[]
     */
    private static $globalDisabledNormalizers = [];

    /**
     * @var string[]
     */
    private static $globalDisabledDenormalizers = [];

    /**
     * @var (NormalizerInterface|DenormalizerInterface)[]
     */
    private $normalizers;

    /**
     * @param iterable $normalizers
     */
    public function __construct(iterable $normalizers)
    {
        $this->normalizers = $normalizers;
    }

    /**
     * @param mixed $object
     * @param string|null $format
     * @param array $context
     * @return mixed
     */
    public function normalize($object, $format = null, array $context = [])
    {
        foreach ($this->normalizers as $normalizer) {
            if ($normalizer instanceof NormalizerInterface
                && $this->isNormalizerEnabled($normalizer, $context)
                && $normalizer->supportsNormalization($object, $format)) {
                return $normalizer->normalize($object, $format, $context);
            }
        }
        throw new UnsupportedException('I can not normalize this object');
    }

    /**
     * @param mixed $data
     * @param string|null $format
     * @return bool
     */
    public function supportsNormalization($data, $format = null)
    {
        foreach ($this->normalizers as $normalizer) {
            if ($normalizer instanceof NormalizerInterface && $this->isNormalizerEnabled($normalizer, [])) {
                if ($normalizer->supportsNormalization($data, $format)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param mixed $data
     * @param string $class
     * @param string|null $format
     * @param array $context
     * @return mixed
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        foreach ($this->normalizers as $denormalizer) {
            if ($denormalizer instanceof DenormalizerInterface
                && $this->isDenormalizerEnabled($denormalizer, $context)
                && $denormalizer->supportsDenormalization($data, $class, $format)) {
                return $denormalizer->denormalize($data, $class, $format, $context);
            }
        }
        throw new UnsupportedException('I can not normalize this object');
    }

    /**
     * @param mixed $data
     * @param string $type
     * @param string|null $format
     * @return bool
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        foreach ($this->normalizers as $denormalizer) {
            if ($denormalizer instanceof DenormalizerInterface && $this->isDenormalizerEnabled($denormalizer, [])) {
                if ($denormalizer->supportsDenormalization($data, $type, $format)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Mark a normalizer as disabled.
     *
     * @param string $className
     */
    public static function disableNormalizer(string $className)
    {
        self::$globalDisabledNormalizers[$className] = true;
    }

    /**
     * Mark a normalizer as enabled.
     *
     * @param string $className
     */
    public static function enableNormalizer(string $className)
    {
        unset(self::$globalDisabledNormalizers[$className]);
    }

    /**
     * Mark a denormalizer as disabled.
     *
     * @param string $className
     */
    public static function disableDenormalizer(string $className)
    {
        self::$globalDisabledDenormalizers[$className] = true;
    }

    /**
     * Mark a denormalizer as enabled.
     *
     * @param string $className
     */
    public static function enableDenormalizer(string $className)
    {
        unset(self::$globalDisabledDenormalizers[$className]);
    }

    /**
     * Returns true if the normalizer is enabled.
     *
     * @param NormalizerInterface $normalizer
     * @param array $context
     * @return bool
     */
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

    /**
     * Returns true if the denormalizer is enabled.
     *
     * @param DenormalizerInterface $normalizer
     * @param array $context
     * @return bool
     */
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
     * Sets the owning Serializer object to the normalizers we have.
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        foreach ($this->normalizers as $normalizer) {
            if ($normalizer instanceof SerializerAwareInterface) {
                $normalizer->setSerializer($serializer);
            }
        }
    }

    /**
     * Sets the owning Denormalizer object.
     */
    public function setDenormalizer(DenormalizerInterface $denormalizer)
    {
        foreach ($this->normalizers as $normalizer) {
            if ($normalizer instanceof DenormalizerAwareInterface) {
                $normalizer->setDenormalizer($denormalizer);
            }
        }
    }

    /**
     * Sets the owning Normalizer object.
     */
    public function setNormalizer(NormalizerInterface $normalizer)
    {
        foreach ($this->normalizers as $normalizer) {
            if ($normalizer instanceof NormalizerAwareInterface) {
                $normalizer->setNormalizer($normalizer);
            }
        }
    }
}
