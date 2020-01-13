<?php
namespace W2w\Lib\Apie\Normalizers;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Exception\MissingConstructorArgumentsException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Throwable;
use W2w\Lib\Apie\Exceptions\ValidationException;

/**
 * Class overriding ObjectNormalizer to workaround https://github.com/symfony/symfony/issues/33622
 */
class ApieObjectNormalizer extends ObjectNormalizer
{
    /**
     * @var PropertyInfoExtractor|null
     */
    private $propertyInfoExtractor;

    /**
     * @param ClassMetadataFactoryInterface|null $classMetadataFactory
     * @param NameConverterInterface|null $nameConverter
     * @param PropertyAccessorInterface|null $propertyAccessor
     * @param PropertyInfoExtractor|null $propertyInfoExtractor
     * @param ClassDiscriminatorResolverInterface|null $classDiscriminatorResolver
     * @param callable|null $objectClassResolver
     * @param array $defaultContext
     */
    public function __construct(
        ClassMetadataFactoryInterface $classMetadataFactory = null,
        NameConverterInterface $nameConverter = null,
        PropertyAccessorInterface $propertyAccessor = null,
        PropertyInfoExtractor $propertyInfoExtractor = null,
        ClassDiscriminatorResolverInterface $classDiscriminatorResolver = null,
        callable $objectClassResolver = null,
        array $defaultContext = []
    ) {
        $this->propertyInfoExtractor = $propertyInfoExtractor;
        parent::__construct(
            $classMetadataFactory,
            $nameConverter,
            $propertyAccessor,
            $propertyInfoExtractor,
            $classDiscriminatorResolver,
            $objectClassResolver,
            $defaultContext
        );
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $context['apie_direction'] = 'read';
        return parent::normalize($object, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $type, $format = null, array $context = [])
    {
        try {
            $context['apie_direction'] = 'write';
            return parent::denormalize($data, $type, $format, $context);
        } catch (NotNormalizableValueException $notNormalizableValueException) {
            $message = $notNormalizableValueException->getMessage();
            // Failed to denormalize attribute "%s" value for class "%s": %s.
            if (preg_match(
                '/^Failed to denormalize attribute "([\\w_]+)" value for class "([\\w\\\\]+)": (.+)\\.$/',
                $message,
                $matches
            )) {
                throw new ValidationException([$matches[1] => [$matches[3]]], $notNormalizableValueException);
            }
            // The type of the "%s" attribute for class "%s" %s.
            if (preg_match(
                '/^The type of the "([\\w_]+)" attribute for class "([\\w\\\\]+)" (.*).$/',
                $message,
                $matches
            )) {
                throw new ValidationException([$matches[1] => [$matches[3]]], $notNormalizableValueException);
            }
            throw $notNormalizableValueException;
        } catch (MissingConstructorArgumentsException $missingConstructorArgumentsException) {
            $message = $missingConstructorArgumentsException->getMessage();
            if (preg_match(
                '/^Cannot create an instance of .* from serialized data because its constructor requires parameter "([\\w_]+)" to be present.$/',
                $message,
                $matches
            )) {
                throw new ValidationException([$matches[1] => [$matches[1] . ' is required']], $missingConstructorArgumentsException);
            }
            throw $missingConstructorArgumentsException;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    protected function setAttributeValue($object, $attribute, $value, $format = null, array $context = [])
    {
        try {
            parent::setAttributeValue($object, $attribute, $value, $format, $context);
        } catch (Throwable $throwable) {
            throw new ValidationException([$attribute => $throwable->getMessage()], $throwable);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function isAllowedAttribute($classOrObject, $attribute, $format = null, array $context = [])
    {
        $isAllowed = parent::isAllowedAttribute(
            $classOrObject,
            $attribute,
            $format,
            $context
        );
        if ($isAllowed && $this->propertyInfoExtractor && !empty($context['apie_direction'])) {
            $className = is_object($classOrObject) ? get_class($classOrObject) : $classOrObject;
            switch ($context['apie_direction']) {
                case 'read':
                    return (bool) $this->propertyInfoExtractor->isReadable($className, $attribute);
                case 'write':
                    if (empty($context['object_to_populate'])) {
                        return $this->propertyInfoExtractor->isWritable($className, $attribute)
                            || $this->propertyInfoExtractor->isInitializable($className, $attribute);
                    }
                    return (bool) $this->propertyInfoExtractor->isWritable($className, $attribute);
            }
        }
        return $isAllowed;
    }
}
