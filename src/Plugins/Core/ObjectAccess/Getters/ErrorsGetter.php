<?php

namespace W2w\Lib\Apie\Plugins\Core\ObjectAccess\Getters;

use Symfony\Component\PropertyInfo\Type;
use W2w\Lib\ApieObjectAccessNormalizer\Exceptions\ValidationException;
use W2w\Lib\ApieObjectAccessNormalizer\Getters\GetterInterface;

/**
 * Mapping getErrors for ValidationException
 *
 * @see ValidationException
 * @internal
 */
class ErrorsGetter implements GetterInterface
{
    public function getName(): string
    {
        return 'errors';
    }

    public function getValue($object)
    {
        return $object->getErrors();
    }

    public function toType(): ?Type
    {
        return new Type(
            Type::BUILTIN_TYPE_ARRAY,
            false,
            null,
            true,
            new Type(Type::BUILTIN_TYPE_STRING),
            new Type(Type::BUILTIN_TYPE_ARRAY)
        );
    }

    public function getPriority(): int
    {
        return 0;
    }
}
