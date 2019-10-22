<?php


namespace W2w\Lib\Apie\ValueObjects;


interface ValueObjectCompareInterface extends ValueObjectInterface
{
    public function equals(ValueObjectInterface $otherObject): boolean;
}
