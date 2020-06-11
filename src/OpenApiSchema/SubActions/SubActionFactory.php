<?php


namespace W2w\Lib\Apie\OpenApiSchema\SubActions;


use ReflectionMethod;
use ReflectionType;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class SubActionFactory
{
    /**
     * @var NameConverterInterface
     */
    private $nameConverter;

    public function __construct(NameConverterInterface $nameConverter)
    {
        $this->nameConverter = $nameConverter;
    }

    public function createFromReflectionMethod(string $name, string $resourceClass, ReflectionMethod $reflectionMethod, ?object $object = null): ?SubAction
    {
        $parameters = $reflectionMethod->getParameters();
        $returnType = null;
        if ($reflectionMethod->getReturnType()) {
            $returnType = $this->fromReflectionType($reflectionMethod->getReturnType());
        }
        if (empty($parameters)) {
            return new SubAction($name, [], $reflectionMethod, $returnType, $object);
        }
        $firstParameter = array_shift($parameters);
        $wantedClass = $firstParameter->getType();
        // if there is no typehint or the typehint is the desired class this sub action is correct.
        if (!$wantedClass || is_a($wantedClass->getName(), $resourceClass, true)) {
            $types = [];
            foreach ($parameters as $parameter) {
                $fieldName = $this->nameConverter->denormalize($parameter->getName());
                $types[$fieldName] = null;
                if ($parameter->getType()) {
                    $types[$fieldName] = $this->fromReflectionType($parameter->getType());
                }
            }
            return new SubAction($name, $types, $reflectionMethod, $returnType, $object);
        }
        return null;
    }

    private function fromReflectionType(ReflectionType $reflectionType): Type
    {
        $nullable = $reflectionType->allowsNull();
        if ($reflectionType->getName() === Type::BUILTIN_TYPE_ARRAY) {
            return new Type(Type::BUILTIN_TYPE_ARRAY, $nullable, null, true);
        }
        if ($reflectionType->getName() === 'void') {
            return new Type(Type::BUILTIN_TYPE_NULL, $nullable);
        }
        if ($reflectionType->isBuiltin()) {
            return new Type($reflectionType->getName(), $nullable);
        }
        return new Type(Type::BUILTIN_TYPE_OBJECT, $nullable, $reflectionType->getName());
    }
}
