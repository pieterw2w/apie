<?php
namespace W2w\Test\Apie\ValueObjects\Data;

use W2w\Lib\Apie\ValueObjects\StringEnumTrait;
use W2w\Lib\Apie\ValueObjects\ValueObjectInterface;

class MockValueObjectWithStringEnumTrait implements ValueObjectInterface
{
    use StringEnumTrait;

    const CONSTANT_A = 'A';

    const CONSTANT_B = 'B';

    const CONSTANT_C = 'A';
}
