<?php

namespace W2w\Lib\Apie\OpenApiSchema;

use erasys\OpenApi\Spec\v3\Schema;
use ReflectionClass;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use W2w\Lib\Apie\PluginInterfaces\DynamicSchemaInterface;
use W2w\Lib\ApieObjectAccessNormalizer\ObjectAccess\FilteredObjectAccess;
use W2w\Lib\ApieObjectAccessNormalizer\ObjectAccess\ObjectAccessInterface;

class OpenApiSchemaGenerator
{
    private const MAX_RECURSION = 3;

    /**
     * @var DynamicSchemaInterface[]
     */
    private $schemaGenerators;

    /**
     * @var Schema[]
     */
    private $predefined = [];

    /**
     * @var Schema[]
     */
    private $alreadyDefined;

    /**
     * @var bool[]
     */
    private $building = [];
    /**
     * @var ObjectAccessInterface
     */
    private $objectAccess;

    /**
     * @var NameConverterInterface
     */
    private $nameConverter;

    /**
     * @var ClassMetadataFactoryInterface
     */
    private $classMetadataFactory;

    /**
     * @param DynamicSchemaInterface $schemaGenerators
     */
    public function __construct(
        array $schemaGenerators,
        ObjectAccessInterface $objectAccess,
        ClassMetadataFactoryInterface $classMetadataFactory,
        NameConverterInterface $nameConverter
    ) {
        $this->schemaGenerators = $schemaGenerators;
        $this->objectAccess = $objectAccess;
        $this->nameConverter = $nameConverter;
        $this->classMetadataFactory = $classMetadataFactory;
    }

    /**
     * Define a resource class and Schema manually.
     * @param string $resourceClass
     * @param Schema $schema
     * @return OpenApiSchemaGenerator
     */
    public function defineSchemaForResource(string $resourceClass, Schema $schema): OpenApiSchemaGenerator
    {
        $this->predefined[$resourceClass] = $schema;
        $this->alreadyDefined = [];

        return $this;
    }

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
        try {
            // specifically defined: just call it.
            if (isset($this->schemaGenerators[$resourceClass])) {
                return $this->schemaGenerators[$resourceClass]($resourceClass, $operation, $groups, $recursion, $this);
            }
            foreach ($this->schemaGenerators as $classDeclaration => $callable) {
                if (is_a($resourceClass, $classDeclaration, true)) {
                    $res = $callable($resourceClass, $operation, $groups, $recursion, $this);
                    if ($res instanceof Schema) {
                        return $res;
                    }
                }
            }
            return null;
        } finally {
            unset($this->building[$cacheKey]);
        }
    }

    private function createSchemaRecursive(string $resourceClass, string $operation, array $groups, int $recursion = 0): Schema
    {
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
        $refl = new ReflectionClass($resourceClass);
        $schema = new Schema([
            'type' => 'object',
            'properties' => [],
            'title' => $refl->getShortName(),
            'description' => $refl->getShortName() . ' ' . $operation . ' for groups ' . implode(', ', $groups),
        ]);
        if ($recursion > self::MAX_RECURSION) {
            return $this->alreadyDefined[$cacheKey] = $schema;
        }
        $objectAccess = $this->filterObjectAccess($this->objectAccess, $resourceClass, $groups);
        switch ($operation) {
            case 'post':
                $constructorArgs = $objectAccess->getConstructorArguments($refl);
                foreach ($constructorArgs as $key => $type) {
                    /** @scrutinizer ignore-call */
                    $fieldName = $this->nameConverter->normalize($key, $resourceClass);
                    $schema->properties[$fieldName] = $this->convertTypeToSchema($type, $operation, $groups, $recursion);
                    $description = $objectAccess->getDescription($refl, $fieldName, false);
                    if ($description) {
                        $schema->properties[$fieldName]->description = $description;
                    }
                }
                // FALLTHROUGH
            case 'put':
                $setterFields = $objectAccess->getSetterFields($refl);
                foreach ($setterFields as $setterField) {
                    /** @scrutinizer ignore-call */
                    $fieldName = $this->nameConverter->normalize($setterField, $resourceClass);
                    $schema->properties[$fieldName] = $this->convertTypesToSchema($objectAccess->getSetterTypes($refl, $setterField), $operation, $groups, $recursion);
                    $description = $objectAccess->getDescription($refl, $fieldName, false);
                    if ($description) {
                        $schema->properties[$fieldName]->description = $description;
                    }
                }
                break;
            case 'get':
                $getterFields = $objectAccess->getGetterFields($refl);
                foreach ($getterFields as $getterField) {
                    /** @scrutinizer ignore-call */
                    $fieldName = $this->nameConverter->normalize($getterField, $resourceClass);
                    $schema->properties[$fieldName] = $this->convertTypesToSchema($objectAccess->getGetterTypes($refl, $getterField), $operation, $groups, $recursion);
                    $description = $objectAccess->getDescription($refl, $fieldName, true);
                    if ($description) {
                        $schema->properties[$fieldName]->description = $description;
                    }
                }
                break;
        }
        return $this->alreadyDefined[$cacheKey] = $schema;
    }

    private function filterObjectAccess(ObjectAccessInterface $objectAccess, string $className, array $groups): ObjectAccessInterface
    {
        $allowedAttributes = [];
        foreach ($this->classMetadataFactory->getMetadataFor($className)->getAttributesMetadata() as $attributeMetadata) {
            $name = $attributeMetadata->getName();
            if (array_intersect($attributeMetadata->getGroups(), $groups)) {
                $allowedAttributes[] = $name;
            }
        }

        return new FilteredObjectAccess($objectAccess, $allowedAttributes);
    }

    private function convertTypesToSchema(array $types, string $operation, array $groups, int $recursion = 0): Schema
    {
        if (empty($types)) {
            return new Schema(['type' => 'object', 'additionalProperties' => true]);
        }
        $type = reset($types);
        return $this->convertTypeToSchema($type, $operation, $groups, $recursion + 1);
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

    protected function convertTypeToSchema(?Type $type, string $operation, array $groups, int $recursion): Schema
    {
        if ($type === null) {
            return new Schema(['type' => 'object', 'additionalProperties' => true]);
        }
        if ($type && $type->getBuiltinType() === Type::BUILTIN_TYPE_OBJECT && $type->getClassName()) {
            return $this->createSchemaRecursive($type->getClassName(), $operation, $groups, $recursion + 1);
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
                    $propertySchema->items = $this->createSchemaRecursive($arrayType->getClassName(), $operation, $groups, $recursion + 1);
                } elseif ($arrayType->getBuiltinType()) {
                    $schemaType = $this->translateType($arrayType->getBuiltinType());
                    $propertySchema->items = new Schema([
                        'type' => $schemaType,
                        'format' => ($schemaType === 'number') ? $arrayType->getBuiltinType() : null,
                    ]);
                }
            }
            return $propertySchema;
        }
        if ($propertySchema->type === 'number') {
            $propertySchema->format = $type->getBuiltinType();
        }
        $className = $type->getClassName();
        if (Type::BUILTIN_TYPE_OBJECT === $type->getBuiltinType() && $recursion < self::MAX_RECURSION && !is_null($className)) {
            return $this->createSchemaRecursive($className, $operation, $groups, $recursion + 1);
        }
        return $propertySchema;
    }
}
