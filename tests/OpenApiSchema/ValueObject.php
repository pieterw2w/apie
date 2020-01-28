<?php
namespace W2w\Test\Apie\OpenApiSchema;

use W2w\Lib\Apie\Interfaces\ValueObjectInterface;
use W2w\Lib\Apie\Plugins\ValueObject\ValueObjects\StringTrait;

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
