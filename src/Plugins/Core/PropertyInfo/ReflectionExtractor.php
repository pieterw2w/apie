<?php

namespace W2w\Lib\Apie\Plugins\Core\PropertyInfo;

use ReflectionMethod;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor as SymfonyReflectionExtractor;

class ReflectionExtractor extends SymfonyReflectionExtractor
{
    private $accessFlags;

    public function __construct(array $mutatorPrefixes = null, array $accessorPrefixes = null, array $arrayMutatorPrefixes = null, bool $enableConstructorExtraction = true, int $accessFlags = self::ALLOW_PUBLIC)
    {
        $this->accessFlags = $accessFlags;
        parent::__construct($mutatorPrefixes, $accessorPrefixes, $arrayMutatorPrefixes, $enableConstructorExtraction, $accessFlags);
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable($class, $property, array $context = []): ?bool
    {
        if ($this->isAllowedProperty($class, $property)) {
            return true;
        }

        list($reflectionMethod) = $this->getAccessorMethod($class, $property);

        return null !== $reflectionMethod;
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable($class, $property, array $context = []): ?bool
    {
        if ($this->isAllowedProperty($class, $property)) {
            return true;
        }

        list($reflectionMethod) = $this->getMutatorMethod($class, $property);

        return null !== $reflectionMethod;
    }

    private function isAllowedProperty(string $class, string $property): bool
    {
        $refl = new ReflectionMethod(SymfonyReflectionExtractor::class, 'isAllowedProperty');
        $refl->setAccessible(true);
        return $refl->invoke($this, $class, $property);
    }


    private function getAccessorMethod(string $class, string $property): ?array
    {
        $refl = new ReflectionMethod(SymfonyReflectionExtractor::class, 'getAccessorMethod');
        $refl->setAccessible(true);
        $result = $refl->invoke($this, $class, $property);
        return $this->filterMethodOnAccessFlag($result);
    }

    private function getMutatorMethod(string $class, string $property): ?array
    {
        $refl = new ReflectionMethod(SymfonyReflectionExtractor::class, 'getMutatorMethod');
        $refl->setAccessible(true);
        $result = $refl->invoke($this, $class, $property);
        return $this->filterMethodOnAccessFlag($result);
    }

    private function filterMethodOnAccessFlag(?array $result): ?array
    {
        if (null === $result) {
            return null;
        }
        /** @var ReflectionMethod $method */
        $method = $result[0];
        if ($this->accessFlags & self::ALLOW_PUBLIC && $method->isPublic()) {
            return $result;
        }

        if ($this->accessFlags & self::ALLOW_PROTECTED && $method->isProtected()) {
            return $result;
        }

        if ($this->accessFlags & self::ALLOW_PRIVATE && $method->isPrivate()) {
            return $result;
        }
        return null;
    }
}
