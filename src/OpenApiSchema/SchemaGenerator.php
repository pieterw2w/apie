<?php

namespace W2w\Lib\Apie\OpenApiSchema;

use erasys\OpenApi\Spec\v3\Schema;
use PhpValueObjects\AbstractStringValueObject;
use ReflectionClass;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Serializer\Mapping\AttributeMetadataInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use W2w\Lib\Apie\ClassResourceConverter;

/**
 * Class that uses symfony/property-info and reflection to create a Schema instance of a class.
 */
class SchemaGenerator
{
    /**
     * @var ClassMetadataFactory
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
     * @param ClassMetadataFactory $classMetadataFactory
     * @param PropertyInfoExtractor $propertyInfoExtractor
     * @param ClassResourceConverter $converter
     * @param NameConverterInterface $nameConverter
     */
    public function __construct(
        ClassMetadataFactory $classMetadataFactory,
        PropertyInfoExtractor $propertyInfoExtractor,
        ClassResourceConverter $converter,
        NameConverterInterface $nameConverter
    ) {
        $this->classMetadataFactory = $classMetadataFactory;
        $this->propertyInfoExtractor = $propertyInfoExtractor;
        $this->converter = $converter;
        $this->nameConverter = $nameConverter;
    }

    /**
     * Define a resource class and Schema manually.
     * @param string $resourceClass
     * @param Schema $schema
     * @return SchemaGenerator
     */
    public function defineSchemaForResource(string $resourceClass, Schema $schema): self
    {
        $this->predefined[$resourceClass] = $schema;
        $this->alreadyDefined = [];

        return $this;
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

        if (is_a($resourceClass, AbstractStringValueObject::class, true)) {
            return $this->alreadyDefined[$cacheKey] = new Schema([
                'type'   => 'string',
                'format' => strtolower((new ReflectionClass($resourceClass))->getShortName()),
            ]);
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
            if ($type instanceof Type) {
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
     * Convert Type into Schema.
     *
     * @param Type $type
     * @param string $operation
     * @param string[] $groups
     * @param int $recursion
     * @return Schema
     */
    private function convertTypeToSchema(Type $type, string $operation, array $groups, int $recursion): Schema
    {
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
                    $propertySchema->items = $this->createSchemaRecursive($arrayType->getClassName(), $operation, $groups, $recursion + 1);
                }
                if ($arrayType->getBuiltinType()) {
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
        if ('object' === $type->getBuiltinType() && $recursion < 2) {
            return $this->createSchemaRecursive($type->getClassName(), $operation, $groups, $recursion + 1);
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
                return $this->propertyInfoExtractor->isReadable($resourceClass, $attributeMetadata->getName());
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
        return $this->createSchemaRecursive($resourceClass, $operation, $groups);
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
