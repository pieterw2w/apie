<?php

namespace W2w\Lib\Apie\OpenApiSchema\Factories;

use erasys\OpenApi\Spec\v3\Schema;
use ReflectionClass;

final class SchemaFactory
{
    public static function createObjectSchemaWithoutProperties(
        ReflectionClass $class,
        string $operation = 'get',
        array $groups = []
    ): Schema {
        $description = $class->getShortName() . ' ' . $operation;
        if ($groups) {
            $description .= ' for groups ' . implode(', ', $groups);
        }
        return new Schema([
            'type' => 'object',
            'properties' => [],
            'title' => $class->getShortName(),
            'description' => $description,
        ]);
    }

    public static function createAnyTypeSchema(): Schema
    {
        return new Schema([]);
    }

    public static function createStringSchema(?string $format = null, ?string $defaultValue = null, bool $nullable = false): Schema
    {
        $data = ['type' => 'string'];
        if ($format !== null) {
            $data['format'] = $format;
        }
        if ($nullable) {
            $data['nullable'] = $nullable;
        }
        if ($defaultValue !== null) {
            $data['example'] = $defaultValue;
            $data['default'] = $defaultValue;
        }
        return new Schema($data);
    }

    public static function createBooleanSchema(): Schema
    {
        return new Schema(['type' => 'boolean']);
    }

    public static function createNumberSchema(?string $format = null): Schema
    {
        return new Schema(['type' => 'number', 'format' => $format]);
    }

    public static function createFloatSchema(): Schema
    {
        return self::createNumberSchema('double');
    }
}
