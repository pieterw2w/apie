<?php
namespace W2w\Lib\Apie\Core;

use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Extracts the identifier from a resource.
 */
class IdentifierExtractor
{
    private $propertyAccessor;

    public function __construct(PropertyAccessor $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * Determines the identifier from a class without having an instance of the class.
     *
     * @param string $className
     * @param array $context
     * @return string|null
     */
    public function getIdentifierKeyOfClass(string $className, array $context = []): ?string
    {
        if (!empty($context['identifier'])) {
            return $context['identifier'];
        }
        $todo = [
            [ReflectionMethod::class, 'getId', 'id'],
            [ReflectionMethod::class, 'id', 'id'],
            [ReflectionProperty::class, 'id', 'id'],
            [ReflectionMethod::class, 'getUuid', 'uuid'],
            [ReflectionMethod::class, 'uuid', 'uuid'],
            [ReflectionProperty::class, 'uuid', 'uuid'],
        ];
        while (!empty($todo)) {
            list($reflectionClass, $property, $result) = array_shift($todo);
            try {
                /** @var ReflectionProperty|ReflectionMethod $test */
                $test = new $reflectionClass($className, $property);
                if ($test->isPublic()) {
                    return $result;
                }
            } catch (ReflectionException $e) {
                $e->getMessage();//ignore
            }
        }
        return null;
    }

    /**
     * Returns the name of the identifier of a resource. If it could not be determined,
     * it returns null.
     *
     * @param mixed $resource
     * @param array $context
     * @return string|null
     */
    public function getIdentifierKey($resource, array $context = []): ?string
    {
        if (isset($context['identifier'])) {
            return $context['identifier'];
        }
        foreach (['id', 'uuid'] as $id) {
            if ($this->propertyAccessor->isReadable($resource, $id)) {
                return $id;
            }
        }
        return null;
    }

    /**
     * Return the value of the identifer of a resource.
     *
     * @param mixed $resource
     * @param array $context
     * @return mixed|null
     */
    public function getIdentifierValue($resource, array $context = [])
    {
        $key = $this->getIdentifierKey($resource, $context);
        if (empty($key)) {
            return null;
        }
        return $this->propertyAccessor->getValue($resource, $key);
    }
}
