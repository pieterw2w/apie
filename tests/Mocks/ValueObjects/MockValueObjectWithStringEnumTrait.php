<?php
namespace W2w\Test\Apie\Mocks\ValueObjects;

use W2w\Lib\Apie\Interfaces\ValueObjectInterface;
use W2w\Lib\Apie\Plugins\ValueObject\ValueObjects\StringEnumTrait;

class MockValueObjectWithStringEnumTrait implements ValueObjectInterface
{
    use StringEnumTrait;

    const CONSTANT_A = 'A';

    const CONSTANT_B = 'B';

    const CONSTANT_C = 'A';
}
