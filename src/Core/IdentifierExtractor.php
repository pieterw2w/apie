<?php
namespace W2w\Lib\Apie\Core;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use W2w\Lib\ApieObjectAccessNormalizer\ObjectAccess\ObjectAccessInterface;

/**
 * Extracts the identifier from a resource.
 */
class IdentifierExtractor
{
    private $objectAccess;

    public function __construct(ObjectAccessInterface $objectAccess)
    {
        $this->objectAccess = $objectAccess;
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
        $fields = $this->objectAccess->getGetterFields(new ReflectionClass($resource));
        foreach (['id', 'uuid'] as $id) {
            if (in_array($id, $fields)) {
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
        return $this->objectAccess->getValue($resource, $key);
    }
}
