<?php
namespace W2w\Lib\Apie\Core\SearchFilters;

use W2w\Lib\Apie\Exceptions\NameAlreadyDefinedException;
use W2w\Lib\ApieObjectAccessNormalizer\Exceptions\NameNotFoundException;

/**
 * Search Filter class is used to filter on the GET all resources action, for example
 * - pagination
 * - search filters
 */
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

    /**
     * @param string $name
     * @return PhpPrimitive
     */
    public function getPrimitiveSearchFilter(string $name): PhpPrimitive
    {
        if (!isset($this->primitiveSearchFilters[$name])) {
            throw new NameNotFoundException($name);
        }
        return $this->primitiveSearchFilters[$name];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasPrimitiveSearchFilter(string $name): bool
    {
        return isset($this->primitiveSearchFilters[$name]);
    }

    /**
     * @return PhpPrimitive[]
     */
    public function getAllPrimitiveSearchFilter(): array
    {
        return $this->primitiveSearchFilters;
    }
}
