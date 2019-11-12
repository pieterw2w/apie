<?php

namespace W2w\Lib\Apie\Normalizers;

use PhpValueObjects\AbstractStringValueObject;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizer that normalizes value objects created with library bruli/php-value-objects
 */
class StringValueObjectNormalizer implements NormalizerInterface, DenormalizerInterface
{
    private function deprecationWarning()
    {
        @trigger_error(
            sprintf('The use of %s is deprecated, use %s instead', __CLASS__, ValueObjectNormalizer::class),
            E_USER_DEPRECATED
        );
    }
    /**
     * @param mixed $data
     * @param string $class
     * @param string|null $format
     * @param array $context
     * @return AbstractStringValueObject
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $this->deprecationWarning();
        return new $class($data);
    }

    /**
     * @param mixed $data
     * @param string $type
     * @param string|null $format
     * @return bool
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return is_a($type, AbstractStringValueObject::class, true);
    }

    /**
     * @param AbstractStringValueObject $object
     * @param string|null $format
     * @param array $context
     * @return string
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $this->deprecationWarning();
        return (string) $object;
    }

    /**
     * @param mixed $data
     * @param string|null $format
     * @return bool
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AbstractStringValueObject;
    }
}
