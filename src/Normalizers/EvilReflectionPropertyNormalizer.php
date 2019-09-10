<?php

namespace W2w\Lib\Apie\Normalizers;

use Exception;
use ReflectionException;
use ReflectionProperty;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * Variation of ObjectNormalizer to be able to set the property even if there is no setter. This can be used by classes
 * implementing ApiResourceRetrieverInterface to set values that have no setter but only have a getter, for example a
 * created_at field in a database table.
 */
class EvilReflectionPropertyNormalizer extends ObjectNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function setAttributeValue($object, $attribute, $value, $format = null, array $context = [])
    {
        try {
            $this->propertyAccessor->setValue($object, $attribute, $value);
        } catch (Exception $exception) {
            try {
                $refl = new ReflectionProperty($object, $attribute);
                $refl->setAccessible(true);
                $refl->setValue($object, $value);
            } catch (ReflectionException $reflException) {
                // ignored
            }
        }
    }
}
