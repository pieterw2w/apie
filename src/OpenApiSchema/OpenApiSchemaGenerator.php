<?php

namespace W2w\Lib\Apie\OpenApiSchema;

use erasys\OpenApi\Spec\v3\Discriminator;
use erasys\OpenApi\Spec\v3\Schema;
use ReflectionClass;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use W2w\Lib\Apie\Core\ClassResourceConverter;
use W2w\Lib\Apie\OpenApiSchema\Factories\SchemaFactory;
use W2w\Lib\Apie\PluginInterfaces\DynamicSchemaInterface;
use W2w\Lib\ApieObjectAccessNormalizer\ObjectAccess\FilteredObjectAccess;
use W2w\Lib\ApieObjectAccessNormalizer\ObjectAccess\ObjectAccessInterface;

class OpenApiSchemaGenerator
{
    private const MAX_RECURSION = 2;

    /**
     * @var DynamicSchemaInterface[]
     */
    private $schemaGenerators;

    /**
     * @var Schema[]
     */
    private $predefined = [];

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
     * @var Schema[]
     */
    protected $alreadyDefined = [];

    /**
     * @var int
     */
    protected $oldRecursion = -1;

    /**
     * @param DynamicSchemaInterface[] $schemaGenerators
     * @param ObjectAccessInterface $objectAccess
     * @param ClassMetadataFactoryInterface $classMetadataFactory
     * @param NameConverterInterface $nameConverter
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
    public function defineSchemaForResource(string $resourceClass, Schema $schema)
    {
        $this->predefined[$resourceClass] = $schema;
        $this->alreadyDefined = [];

        return $this;
    }

    /**
     * Creates a Schema for  specific resource class.
     *
     * @param string $resourceClass
     * @param string $operation
     * @param array $groups
     * @return Schema
     */
    public function createSchema(string $resourceClass, string $operation, array $groups): Schema
    {
        return unserialize(serialize($this->createSchemaRecursive($resourceClass, $operation, $groups, $this->oldRecursion + 1)));
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
        $oldValue = $this->oldRecursion;
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
            $this->oldRecursion = $oldValue;
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
        $schema = SchemaFactory::createObjectSchemaWithoutProperties($refl, $operation, $groups);

        // if definition is an interface or abstract base class it is possible that it has additional properties.
        if ($refl->isAbstract() || $refl->isInterface()) {
            $schema->additionalProperties = true;
        }
        if ($recursion > self::MAX_RECURSION) {
            $schema->properties = null;
            $schema->additionalProperties = true;
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
                    $description = $objectAccess->getDescription($refl, $key, false);
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
                    $description = $objectAccess->getDescription($refl, $setterField, false);
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
                    $description = $objectAccess->getDescription($refl, $getterField, true);
                    if ($description) {
                        $schema->properties[$fieldName]->description = $description;
                    }
                }
                break;
        }
        if (is_array($schema->properties) && empty($schema->properties)) {
            $schema->properties = null;
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
            return SchemaFactory::createAnyTypeSchema();
        }
        $type = reset($types);
        // this is only because this serializer does not do a deep populate.
        if ($operation === 'put') {
            $operation = 'post';
        }
        return $this->convertTypeToSchema($type, $operation, $groups, $recursion);
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

    /**
     * Convert Type into Schema.
     *
     * @param Type $type
     * @param string $operation
     * @param string[] $groups
     * @param int $recursion
     * @internal
     *
     * @return Schema
     */
    public function convertTypeToSchema(?Type $type, string $operation, array $groups, int $recursion): Schema
    {
        if ($type === null) {
            return SchemaFactory::createAnyTypeSchema();
        }
        if ($type && $type->getBuiltinType() === Type::BUILTIN_TYPE_OBJECT && $type->getClassName() && !$type->isCollection()) {
            $this->oldRecursion++;
            try {
                return $this->createSchemaRecursive($type->getClassName(), $operation, $groups, $recursion + 1);
            } finally {
                $this->oldRecursion--;
            }

        }
        $propertySchema = new Schema([
            'type'        => 'string',
            'nullable'    => true,
        ]);
        $propertySchema->type = $this->translateType($type->getBuiltinType());
        if ($propertySchema->type === 'array') {
            $propertySchema->items = SchemaFactory::createAnyTypeSchema();
        }
        if (!$type->isNullable()) {
            $propertySchema->nullable = false;
        }
        if ($type->isCollection()) {
            $propertySchema->type = 'array';
            $propertySchema->items = new Schema([]);
            $arrayType = $type->getCollectionValueType();
            if ($arrayType) {
                if ($arrayType->getClassName()) {
                    $this->oldRecursion++;
                    try {
                        $propertySchema->items = $this->createSchemaRecursive(
                            $arrayType->getClassName(),
                            $operation,
                            $groups,
                            $recursion + 1
                        );
                    } finally {
                        $this->oldRecursion--;
                    }
                } elseif ($arrayType->getBuiltinType()) {
                    $schemaType = $this->translateType($arrayType->getBuiltinType());
                    $propertySchema->items = new Schema([
                        'type' => $schemaType,
                        'format' => ($schemaType === 'number') ? $arrayType->getBuiltinType() : null,
                    ]);
                    //array[] typehint...
                    if ($schemaType === 'array') {
                        $propertySchema->items->items = SchemaFactory::createAnyTypeSchema();
                    }
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
                $properties[$discriminatorColumn] = SchemaFactory::createStringSchema(null, $keyValue);
            }
            $subschemas[$subclass]->properties = $properties;
        }
        $this->alreadyDefined[$cacheKey . ',0'] = new Schema([
            'type' => 'object',
            'properties' => [
                $discriminatorColumn => SchemaFactory::createStringSchema(),
            ],
            'oneOf' => array_values($subschemas),
            'discriminator' => new Discriminator($discriminatorColumn, $discriminatorMapping)
        ]);
        for ($i = 1; $i < self::MAX_RECURSION; $i++) {
            $this->alreadyDefined[$cacheKey . ',' . $i] = $this->alreadyDefined[$cacheKey . ',0'];
        }
        return $this->alreadyDefined[$cacheKey . ',0'];
    }
}
