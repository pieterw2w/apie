<?php


namespace W2w\Test\Apie\ValueObjects\Data;


use W2w\Lib\Apie\ValueObjects\StringTrait;
use W2w\Lib\Apie\ValueObjects\ValueObjectInterface;

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
