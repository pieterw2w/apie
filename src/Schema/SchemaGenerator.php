<?php

namespace W2w\Lib\Apie\Schema;

use W2w\Lib\Apie\ClassResourceConverter;
use erasys\OpenApi\Spec\v3\Schema;
use PhpValueObjects\AbstractStringValueObject;
use ReflectionClass;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Serializer\Mapping\AttributeMetadataInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class SchemaGenerator
{
    private $classMetadataFactory;

    private $propertyInfoExtractor;

    private $converter;

    private $nameConverter;

    private $alreadyDefined = [];

    private $predefined = [];

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

    public function defineSchemaForResource(string $resourceClass, Schema $schema): self
    {
        $this->predefined[$resourceClass] = $schema;
        $this->alreadyDefined = [];

        return $this;
    }

    private function createSchemaRecursive(string $resourceClass, string $operation, array $groups, int $recursion = 0)
    {
        $metaData = $this->classMetadataFactory->getMetadataFor($resourceClass);
        $cacheKey = $this->getCacheKey($resourceClass, $operation, $groups) . ',' . $recursion;
        if (isset($this->alreadyDefined[$cacheKey])) {
            if ($recursion > 1) {
                return new Schema(['type' => 'string']);
            }

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
            $propertySchema = new Schema([
                'type'        => 'string',
                'description' => $this->propertyInfoExtractor->getShortDescription($resourceClass, $attributeMetadata->getName()),
                'nullable'    => true,
            ]);
            $properties[$name] = $propertySchema;
            $types = $this->propertyInfoExtractor->getTypes($resourceClass, $attributeMetadata->getName()) ?? [];
            $type = reset($types);
            if ($type instanceof Type) {
                $propertySchema->type = $this->translateType($type->getBuiltinType());
                if (!$type->isNullable()) {
                    $propertySchema->nullable = false;
                }
                if ($type->getBuiltinType() === 'array') {
                    $propertySchema->items = new Schema([
                        'oneOf' => [
                            new Schema(['type' => 'string', 'nullable' => true]),
                            new Schema(['type' => 'integer']),
                            new Schema(['type' => 'boolean']),
                        ],
                    ]);
                }
                if ('object' === $type->getBuiltinType() && $recursion < 2) {
                    $propertySchema = $this->createSchemaRecursive($type->getClassName(), $operation, $groups, $recursion + 1);
                    $properties[$name] = $propertySchema;
                }
            }
        }
        $schema->properties = $properties;
        $this->alreadyDefined[$cacheKey] = $schema;

        return $schema;
    }

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

        return true;
    }

    public function createSchema(string $resourceClass, string $operation, array $groups)
    {
        return $this->createSchemaRecursive($resourceClass, $operation, $groups);
    }

    private function getCacheKey(string $resourceClass, string $operation, array $groups)
    {
        return $resourceClass . ',' . $operation . ',' . implode(', ', $groups);
    }

    private function determineReadWrite(string $operation): string
    {
        if ($operation === 'post' || $operation === 'put') {
            return 'write';
        }

        return 'read';
    }

    private function translateType(string $type): string
    {
        switch ($type) {
            case 'int': return 'integer';
            case 'bool': return 'boolean';
        }

        return $type;
    }
}
