<?php
namespace W2w\Test\Apie\OpenApiSchema;

use W2w\Lib\Apie\ValueObjects\StringTrait;
use W2w\Lib\Apie\ValueObjects\ValueObjectInterface;

class ValueObject implements ValueObjectInterface
{
    use StringTrait;

    protected function validValue(string $value): bool
    {
        return $value !== '';
    }

    protected function sanitizeValue(string $value): string
    {
        return $value;
    }
}
