<?php
namespace W2w\Lib\Apie\SearchFilters;

use W2w\Lib\Apie\Exceptions\NameAlreadyDefinedException;
use W2w\Lib\Apie\Exceptions\NameNotFoundException;
use W2w\Lib\Apie\ValueObjects\PhpPrimitive;

final class SearchFilter
{
    /**
     * @var PhpPrimitive[]
     */
    private $primitiveSearchFilters = [];

    /**
     * @param string $name
     * @param PhpPrimitive|string $primitive
     * @return SearchFilter
     */
    public function addPrimitiveSearchFilter(string $name, $primitive): self
    {
        if (!($primitive instanceof PhpPrimitive)) {
            $primitive = PhpPrimitive::fromNative(strtoupper($primitive));
        }
        if (isset($this->primitiveSearchFilters[$name])) {
            throw new NameAlreadyDefinedException($name);
        }
        $this->primitiveSearchFilters[$name] = $primitive;
        return $this;
    }

    public function getPrimitiveSearchFilter(string $name): PhpPrimitive
    {
        if (!isset($this->primitiveSearchFilters[$name])) {
            throw new NameNotFoundException($name);
        }
        return $this->primitiveSearchFilters[$name];
    }

    public function hasPrimitiveSearchFilter(string $name): bool
    {
        return isset($this->primitiveSearchFilters[$name]);
    }

    public function getAllPrimitiveSearchFilter(): array
    {
        return $this->primitiveSearchFilters;
    }
}
