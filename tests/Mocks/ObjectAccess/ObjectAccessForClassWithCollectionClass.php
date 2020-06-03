<?php


namespace W2w\Test\Apie\Mocks\ObjectAccess;

use ReflectionClass;
use Symfony\Component\PropertyInfo\Type;
use Tightenco\Collect\Support\Collection;
use W2w\Lib\ApieObjectAccessNormalizer\ObjectAccess\ObjectAccess;
use W2w\Lib\ApieObjectAccessNormalizer\ObjectAccess\ObjectAccessSupportedInterface;
use W2w\Test\Apie\Mocks\ApiResources\SumExample;
use W2w\Test\Apie\Mocks\ValueObjects\ObjectWithCollection;

final class ObjectAccessForClassWithCollectionClass extends ObjectAccess implements ObjectAccessSupportedInterface
{
    /**
     * @var Type
     */
    private $collectionTypehint;

    /**
     * @var Type
     */
    private $optionalCollectionTypehint;

    public function __construct()
    {
        $this->collectionTypehint = new Type(
            Type::BUILTIN_TYPE_OBJECT,
            false,
            Collection::class,
            true,
            new Type(Type::BUILTIN_TYPE_INT),
            new Type(Type::BUILTIN_TYPE_OBJECT, false, SumExample::class)
        );
        $this->optionalCollectionTypehint = new Type(
            Type::BUILTIN_TYPE_OBJECT,
            true,
            Collection::class,
            true,
            new Type(Type::BUILTIN_TYPE_INT),
            new Type(Type::BUILTIN_TYPE_OBJECT, false, SumExample::class)
        );
        parent::__construct(true, true);
    }

    /**
     * {@inheritDoc}
     */
    public function isSupported(ReflectionClass $reflectionClass): bool
    {
        return $reflectionClass->name === ObjectWithCollection::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getGetterTypes(ReflectionClass $reflectionClass, string $fieldName): array
    {
        if ($fieldName === 'collection') {
            return [$this->collectionTypehint];
        }
        if ($fieldName === 'optionalCollection') {
            return [$this->optionalCollectionTypehint];
        }
        return parent::getGetterTypes($reflectionClass, $fieldName);
    }

    /**
     * {@inheritDoc}
     */
    public function getSetterTypes(ReflectionClass $reflectionClass, string $fieldName): array
    {
        if ($fieldName === 'collection') {
            return [$this->collectionTypehint];
        }
        if ($fieldName === 'optionalCollection') {
            return [$this->optionalCollectionTypehint];
        }
        return parent::getSetterTypes($reflectionClass, $fieldName);
    }
}
