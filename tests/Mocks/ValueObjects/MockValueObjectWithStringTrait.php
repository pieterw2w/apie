<?php
namespace W2w\Test\Apie\Mocks\ValueObjects;

use W2w\Lib\Apie\Interfaces\ValueObjectInterface;
use W2w\Lib\Apie\Plugins\ValueObject\ValueObjects\StringTrait;

class MockValueObjectWithStringTrait implements ValueObjectInterface
{
    use StringTrait;

    public $validValueRetrieved;

    protected function validValue(string $value): bool
    {
        $this->validValueRetrieved = $value;
        return $value !== 'blha';
    }

    protected function sanitizeValue(string $value): string
    {
        return strtoupper($value);
    }
}
