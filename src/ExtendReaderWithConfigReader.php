<?php
namespace W2w\Lib\Apie;

use Doctrine\Common\Annotations\Reader;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use W2w\Lib\Apie\Annotations\ApiResource;

/**
 * Extend the annotation reader with a config array to override the actual annotations if required. Should only
 * work on the ApiResource annotation.
 */
class ExtendReaderWithConfigReader implements Reader
{
    private $reader;

    private $config;

    /**
     * @param Reader $reader
     * @param ApiResource[] $config
     */
    public function __construct(Reader $reader, array $config)
    {
        $this->reader = $reader;
        $this->config = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function getClassAnnotations(ReflectionClass $class)
    {
        $className = $class->getName();
        $annotations = $this->reader->getClassAnnotations($class);
        if (isset($this->config[$className])) {
            $annotations = array_filter($annotations, function ($annotation) {
                return !($annotation instanceof ApiResource);
            });
            $annotations[] = $this->config[$className];
        }

        return $annotations;
    }

    /**
     * {@inheritDoc}
     */
    public function getClassAnnotation(ReflectionClass $class, $annotationName)
    {
        $className = $class->getName();
        if ($annotationName === ApiResource::class && isset($this->config[$className])) {
            return $this->config[$className];
        }
        return $this->reader->getClassAnnotation($class, $annotationName);
    }

    /**
     * {@inheritDoc}
     */
    public function getMethodAnnotations(ReflectionMethod $method)
    {
        return $this->reader->getMethodAnnotations($method);
    }

    /**
     * {@inheritDoc}
     */
    public function getMethodAnnotation(ReflectionMethod $method, $annotationName)
    {
        return $this->reader->getMethodAnnotation($method, $annotationName);
    }

    /**
     * {@inheritDoc}
     */
    public function getPropertyAnnotations(ReflectionProperty $property)
    {
        return $this->reader->getPropertyAnnotations($property);
    }

    /**
     * {@inheritDoc}
     */
    public function getPropertyAnnotation(ReflectionProperty $property, $annotationName)
    {
        return $this->reader->getPropertyAnnotation($property, $annotationName);
    }
}
