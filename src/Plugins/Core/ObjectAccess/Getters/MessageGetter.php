<?php


namespace W2w\Lib\Apie\Plugins\Core\ObjectAccess\Getters;

use Symfony\Component\PropertyInfo\Type;
use W2w\Lib\ApieObjectAccessNormalizer\Getters\GetterInterface;

/**
 * Maps getMessage from exceptions.
 *
 * @internal
 */
class MessageGetter implements GetterInterface
{
    public function getName(): string
    {
        return 'message';
    }

    public function getValue($object)
    {
        return $object->getMessage();
    }

    public function toType(): ?Type
    {
        return new Type(Type::BUILTIN_TYPE_STRING);
    }

    public function getPriority(): int
    {
        return 0;
    }
}
