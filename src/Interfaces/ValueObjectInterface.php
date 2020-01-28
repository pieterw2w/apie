<?php
namespace W2w\Lib\Apie\Interfaces;

use erasys\OpenApi\Spec\v3\Schema;

interface ValueObjectInterface
{
    /**
     * Converts a native value into a value object.
     *
     * @param mixed $value
     * @return self
     */
    public static function fromNative($value);

    /**
     * Converts value object into a native value.
     *
     * @return mixed
     */
    public function toNative();

    /**
     * Returns Schema for OpenAPI spec.
     *
     * @return Schema
     */
    public static function toSchema(): Schema;
}
