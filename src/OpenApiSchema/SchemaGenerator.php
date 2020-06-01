<?php

namespace W2w\Lib\Apie\OpenApiSchema;

use erasys\OpenApi\Spec\v3\Discriminator;
use erasys\OpenApi\Spec\v3\Schema;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Serializer\Mapping\AttributeMetadataInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use W2w\Lib\Apie\Core\ClassResourceConverter;

/**
 * Class that uses symfony/property-info and reflection to create a Schema instance of a class.
 * @deprecated use OpenApiSchemaGenerator
 */
class SchemaGenerator
{
    private const MAX_RECURSION = 2;

    /**
     * @var ClassMetadataFactoryInterface
     */
    private $classMetadataFactory;

    /**
     * @var PropertyInfoExtractor
     */
    private $propertyInfoExtractor;

    /**
     * @var ClassResourceConverter
     */
    private $converter;

    /**
     * @var NameConverterInterface
     */
    private $nameConverter;

    /**
     * @var Schema[]
     */
    private $alreadyDefined = [];

    /**
     * @var Schema[]
     */
    private $predefined = [];

    /**
     * @var callable[]
     */
    private $schemaCallbacks = [];

    /**
     * @var bool[]
     */
    private $building = [];

    /**
     * @var int
     */
    private $oldRecursion = -1;

    /**
     * @param ClassMetadataFactoryInterface $classMetadataFactory
     * @param PropertyInfoExtractor $propertyInfoExtractor
     * @param ClassResourceConverter $converter
     * @param NameConverterInterface $nameConverter
     * @param callable[] $schemaCallbacks
     */
    public function __construct(
        ClassMetadataFactoryInterface $classMetadataFactory,
        PropertyInfoExtractor $propertyInfoExtractor,
        ClassResourceConverter $converter,
        NameConverterInterface $nameConverter,
        array $schemaCallbacks = []
    ) {
        $this->classMetadataFactory = $classMetadataFactory;
        $this->propertyInfoExtractor = $propertyInfoExtractor;
        $this->converter = $converter;
        $this->nameConverter = $nameConverter;
        $this->schemaCallbacks = $schemaCallbacks;
    }

    /**
     * Define a resource class and Schema manually.
     * @param string $resourceClass
     * @param Schema $schema
     * @return SchemaGenerator
     */
    public function defineSchemaForResource(string $resourceClass, Schema $schema)
    {
        $this->predefined[$resourceClass] = $schema;
        $this->alreadyDefined = [];

        return $this;
    }

    /**
     * Define an OpenAPI discriminator spec for an interface or base class that have a discriminator column.
     *
     * @param string $resourceInterface
     * @param string $discriminatorColumn
     * @param array $subclasses
     * @param string $operation
     * @param string[] $groups
     * @return Schema
     */
    public function defineSchemaForPolymorphicObject(
        string $resourceInterface,
        string $discriminatorColumn,
        array $subclasses,
        string $operation = 'get',
        array $groups = ['get', 'read']
    ): Schema {
        $cacheKey = $this->getCacheKey($resourceInterface, $operation, $groups);
        /** @var Schema[] $subschemas */
        $subschemas = [];
        $discriminatorMapping = [];
        foreach ($subclasses as $keyValue => $subclass) {
            $subschemas[$subclass] = $discriminatorMapping[$keyValue] = $this->createSchema($subclass, $operation, $groups);
            $properties = $subschemas[$subclass]->properties;
            if (isset($properties[$discriminatorColumn])) {
                $properties[$discriminatorColumn]->default = $keyValue;
                $properties[$discriminatorColumn]->example = $keyValue;
            } else {
                $properties[$discriminatorColumn] = new Schema([
                    'type' => 'string',
                    'default' => $keyValue,
                    'example' => $keyValue
                ]);
            }
            $subschemas[$subclass]->properties = $properties;
        }
        $this->alreadyDefined[$cacheKey . ',0'] = new Schema([
            'type' => 'object',
            'properties' => [
                $discriminatorColumn => new Schema(['type' => 'string']),
            ],
            'oneOf' => array_values($subschemas),
            'discriminator' => new Discriminator($discriminatorColumn, $discriminatorMapping)
        ]);
        for ($i = 1; $i < self::MAX_RECURSION; $i++) {
            $this->alreadyDefined[$cacheKey . ',' . $i] = $this->alreadyDefined[$cacheKey . ',0'];
        }
        return $this->alreadyDefined[$cacheKey . ',0'];
    }

    /**
     * Creates a schema recursively.
     *
     * @param string $resourceClass
     * @param string $operation
     * @param string[] $groups
     * @param int $recursion
     * @return Schema
     */
    private function createSchemaRecursive(string $resourceClass, string $operation, array $groups, int $recursion = 0): Schema
    {
        $metaData = $this->classMetadataFactory->getMetadataFor($resourceClass);
        $cacheKey = $this->getCacheKey($resourceClass, $operation, $groups) . ',' . $recursion;
        if (isset($this->alreadyDefined[$cacheKey])) {
            return $this->alreadyDefined[$cacheKey];
        }

        foreach ($this->predefined as $className => $schema) {
            if (is_a($resourceClass, $className, true)) {
                $this->alreadyDefined[$cacheKey] = $schema;

                return $this->alreadyDefined[$cacheKey];
            }
        }

        if ($predefinedSchema = $this->runCallbacks($cacheKey, $resourceClass, $operation, $groups, $recursion)) {
            return $this->alreadyDefined[$cacheKey] = $predefinedSchema;
        }

        $name = $this->converter->normalize($resourceClass);
        $this->alreadyDefined[$cacheKey] = $schema = new Schema([
            'title'       => $name,
            'description' => $name . ' ' . $operation,
            'type'        => 'object',
        ]);
        if ($groups) {
            $schema->description .= ' for groups ' . implode(', ', $groups);
        }
        $properties = [];
        foreach ($metaData->getAttributesMetadata() as $attributeMetadata) {
            $name = $attributeMetadata->getSerializedName() ?? $this->nameConverter->normalize($attributeMetadata->getName());
            if (!$this->isPropertyApplicable($resourceClass, $attributeMetadata, $operation, $groups)) {
                continue;
            }
            $properties[$name] = new Schema([
                'type'        => 'string',
                'nullable'    => true,
            ]);
            $types = $this->propertyInfoExtractor->getTypes($resourceClass, $attributeMetadata->getName()) ?? [];
            $type = reset($types);
            if ($type instanceof Type && ($recursion < (1 + self::MAX_RECURSION))) {
                $properties[$name] = $this->convertTypeToSchema($type, $operation, $groups, $recursion);
            }
            if (!$properties[$name]->description) {
                $properties[$name]->description = $this->propertyInfoExtractor->getShortDescription(
                    $resourceClass,
                    $attributeMetadata->getName()
                );
            }
        }
        $schema->properties = $properties;
        $this->alreadyDefined[$cacheKey] = $schema;

        return $schema;
    }

    /**
     * Iterate over a list of callbacks to see if they provide a schema for this resource class.
     *
     * @param string $cacheKey
     * @param string $resourceClass
     * @param string $operation
     * @param array $groups
     * @param int $recursion
     *
     * @return Schema|null
     */
    private function runCallbacks(string $cacheKey, string $resourceClass, string $operation, array $groups, int $recursion): ?Schema
    {
        if (!empty($this->building[$cacheKey])) {
            return null;
        }
        $this->building[$cacheKey] = true;
        $oldValue = $this->oldRecursion;
        try {
            // specifically defined: just call it.
            if (isset($this->schemaCallbacks[$resourceClass])) {
                return $this->schemaCallbacks[$resourceClass]($resourceClass, $operation, $groups, $recursion, $this);
            }
            foreach ($this->schemaCallbacks as $classDeclaration => $callable) {
                if (is_a($resourceClass, $classDeclaration, true)) {
                    $res = $callable($resourceClass, $operation, $groups, $recursion, $this);
                    if ($res instanceof Schema) {
                        return $res;
                    }
                }
            }
            return null;
        } finally {
            $this->oldRecursion = $oldValue;
            unset($this->building[$cacheKey]);
        }
    }

    /**
     * Convert Type into Schema.
     *
     * @param Type $type
     * @param string $operation
     * @param string[] $groups
     * @param int $recursion
     *
     * @return Schema
     */
    protected function convertTypeToSchema(?Type $type, string $operation, array $groups, int $recursion): Schema
    {
        if ($type === null) {
            return new Schema(['type' => 'object', 'additionalProperties' => true]);
        }
        $propertySchema = new Schema([
            'type'        => 'string',
            'nullable'    => true,
        ]);
        $propertySchema->type = $this->translateType($type->getBuiltinType());
        if (!$type->isNullable()) {
            $propertySchema->nullable = false;
        }
        if ($type->isCollection()) {
            $propertySchema->type = 'array';
            $propertySchema->items = new Schema([
                'oneOf' => [
                    new Schema(['type' => 'string', 'nullable' => true]),
                    new Schema(['type' => 'integer']),
                    new Schema(['type' => 'boolean']),
                ],
            ]);
            $arrayType = $type->getCollectionValueType();
            if ($arrayType) {
                if ($arrayType->getClassName()) {
                    $this->oldRecursion++;
                    try {
                        $propertySchema->items = $this->createSchemaRecursive(
                            $arrayType->getClassName(), $operation, $groups, $recursion + 1
                        );
                    } finally {
                        $this->oldRecursion--;
                    }
                } elseif ($arrayType->getBuiltinType()) {
                    $type = $this->translateType($arrayType->getBuiltinType());
                    $propertySchema->items = new Schema([
                        'type' => $type,
                        'format' => ($type === 'number') ? $arrayType->getBuiltinType() : null,
                    ]);
                }
            }
            return $propertySchema;
        }
        if ($propertySchema->type === 'number') {
            $propertySchema->format = $type->getBuiltinType();
        }
        $className = $type->getClassName();
        if ('object' === $type->getBuiltinType() && $recursion < self::MAX_RECURSION && !is_null($className)) {
            $this->oldRecursion++;
            try {
                return $this->createSchemaRecursive($className, $operation, $groups, $recursion + 1);
            } finally {
                $this->oldRecursion--;
            }
        }
        return $propertySchema;
    }

    /**
     * Returns true if a property is applicable for a specific operation and a specific serialization group.
     *
     * @param string $resourceClass
     * @param AttributeMetadataInterface $attributeMetadata
     * @param string $operation
     * @param string[] $groups
     * @return bool
     */
    private function isPropertyApplicable(string $resourceClass, AttributeMetadataInterface $attributeMetadata, string $operation, array $groups): bool
    {
        if (!array_intersect($attributeMetadata->getGroups(), $groups)) {
            return false;
        }
        switch ($operation) {
            case 'put':
                return $this->propertyInfoExtractor->isReadable($resourceClass, $attributeMetadata->getName())
                    && $this->propertyInfoExtractor->isWritable($resourceClass, $attributeMetadata->getName());
            case 'get':
                return (bool) $this->propertyInfoExtractor->isReadable($resourceClass, $attributeMetadata->getName());
            case 'post':
                return $this->propertyInfoExtractor->isWritable($resourceClass, $attributeMetadata->getName())
                    || $this->propertyInfoExtractor->isInitializable($resourceClass, $attributeMetadata->getName());
        }

        // @codeCoverageIgnoreStart
        return true;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Returns a Schema for a resource class, operation and serialization group tuple.
     *
     * @param string $resourceClass
     * @param string $operation
     * @param string[] $groups
     * @return Schema
     */
    public function createSchema(string $resourceClass, string $operation, array $groups): Schema
    {
        return $this->createSchemaRecursive($resourceClass, $operation, $groups, $this->oldRecursion + 1);
    }

    /**
     * Creates a unique cache key to be used for already defined schemas for performance reasons.
     *
     * @param string $resourceClass
     * @param string $operation
     * @param string[] $groups
     * @return string
     */
    private function getCacheKey(string $resourceClass, string $operation, array $groups)
    {
        return $resourceClass . ',' . $operation . ',' . implode(', ', $groups);
    }

    /**
     * Returns OpenApi property type for scalars.
     *
     * @param string $type
     * @return string
     */
    private function translateType(string $type): string
    {
        switch ($type) {
            case 'int': return 'integer';
            case 'bool': return 'boolean';
            case 'float': return 'number';
            case 'double': return 'number';
        }

        return $type;
    }
}
