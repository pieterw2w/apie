<?php


namespace W2w\Lib\Apie\ValueObjects;

/**
 * Interface for value objects to compare value objects.
 */
interface ValueObjectCompareInterface extends ValueObjectInterface
{
    /**
     * Returns true if 2 value objects have the same value.
     *
     * @param ValueObjectInterface $otherObject
     * @return bool
     */
    public function equals(ValueObjectInterface $otherObject): bool;
}
