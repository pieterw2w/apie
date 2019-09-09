<?php

namespace W2w\Lib\Apie\Normalizer;

use Exception;
use ReflectionException;
use ReflectionProperty;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class EvilReflectionPropertyNormalizer extends ObjectNormalizer
{
    public function __construct(
        ClassMetadataFactoryInterface $classMetadataFactory = null,
        NameConverterInterface $nameConverter = null,
        PropertyAccessorInterface $propertyAccessor = null,
        PropertyTypeExtractorInterface $propertyTypeExtractor = null,
        ClassDiscriminatorResolverInterface $classDiscriminatorResolver = null,
        callable $objectClassResolver = null,
        array $defaultContext = []
    ) {
        parent::__construct($classMetadataFactory, $nameConverter, $propertyAccessor, $propertyTypeExtractor, $classDiscriminatorResolver, $objectClassResolver, $defaultContext);
    }

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
