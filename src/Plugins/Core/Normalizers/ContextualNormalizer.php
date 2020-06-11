<?php
namespace W2w\Lib\Apie\Plugins\Core\Normalizers;

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
 *
 * @deprecated use ObjectAccess instead
 */
class ContextualNormalizer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    /**
     * @var boolean[]
     */
    private static $globalDisabledNormalizers = [];

    /**
     * @var boolean[]
     */
    private static $globalDisabledDenormalizers = [];

    /**
     * @var iterable<NormalizerInterface|DenormalizerInterface>
     */
    private $normalizers;

    /**
     * @param iterable<NormalizerInterface|DenormalizerInterface> $normalizers
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
                && $this->isThisNormalizerEnabled($normalizer)
                && $normalizer->supportsNormalization($object, $format)) {
                return $normalizer->normalize($object, $format, $context);
            }
        }
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd
    /**
     * @param mixed $data
     * @param string|null $format
     * @return bool
     */
    public function supportsNormalization($data, $format = null)
    {
        foreach ($this->normalizers as $normalizer) {
            if ($normalizer instanceof NormalizerInterface
                && $this->isThisNormalizerEnabled($normalizer)
                && $normalizer->supportsNormalization($data, $format)) {
                return true;
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
                && $this->isDenormalizerEnabled($denormalizer)
                && $denormalizer->supportsDenormalization($data, $class, $format)) {
                return $denormalizer->denormalize($data, $class, $format, $context);
            }
        }
        // @codeCoverageIgnoreStart
    }

    // @codeCoverageIgnoreEnd

    /**
     * @param mixed $data
     * @param string $type
     * @param string|null $format
     * @return bool
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        foreach ($this->normalizers as $denormalizer) {
            if ($denormalizer instanceof DenormalizerInterface
                && $this->isDenormalizerEnabled($denormalizer)
                && $denormalizer->supportsDenormalization($data, $type, $format)) {
                return true;
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

    public static function isNormalizerEnabled(string $className): bool
    {
        return empty(self::$globalDisabledNormalizers[$className]);
    }

    /**
     * Returns true if the normalizer is enabled.
     *
     * @param NormalizerInterface $normalizer
     * @return bool
     */
    private function isThisNormalizerEnabled(NormalizerInterface $normalizer): bool
    {
        return self::isNormalizerEnabled(get_class($normalizer));
    }

    /**
     * Returns true if the denormalizer is enabled.
     *
     * @param DenormalizerInterface $normalizer
     * @return bool
     */
    private function isDenormalizerEnabled(DenormalizerInterface $normalizer): bool
    {
        $className = get_class($normalizer);

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
    public function setNormalizer(NormalizerInterface $owningNormalizer)
    {
        foreach ($this->normalizers as $normalizer) {
            if ($normalizer instanceof NormalizerAwareInterface) {
                $normalizer->setNormalizer($owningNormalizer);
            }
        }
    }
}
