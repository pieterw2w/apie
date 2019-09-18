<?php
namespace W2w\Lib\Apie\Normalizers;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * Class overriding ObjectNormalizer to workaround https://github.com/symfony/symfony/issues/33622
 */
class ApieObjectNormalizer extends ObjectNormalizer
{
    /**
     * @var PropertyInfoExtractor
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
        $context['apie_direction'] = 'write';
        return parent::denormalize($data, $type, $format, $context);
    }

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
                    return $this->propertyInfoExtractor->isReadable($className, $attribute);
                case 'write':
                    if (empty($context['object_to_populate'])) {
                        return $this->propertyInfoExtractor->isWritable($className, $attribute)
                            || $this->propertyInfoExtractor->isInitializable($className, $attribute);
                    }
                    return $this->propertyInfoExtractor->isWritable($className, $attribute);
            }
        }
        return $isAllowed;
    }
}
