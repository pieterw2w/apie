<?php

namespace W2w\Lib\Apie\Plugins\Core\ResourceFactories;

use ReflectionClass;
use ReflectionException;
use W2w\Lib\Apie\Core\IdentifierExtractor;
use W2w\Lib\Apie\Exceptions\CouldNotConstructApiResourceClassException;
use W2w\Lib\Apie\Exceptions\InvalidClassTypeException;
use W2w\Lib\Apie\Interfaces\ApiResourceFactoryInterface;
use W2w\Lib\Apie\Interfaces\ApiResourcePersisterInterface;
use W2w\Lib\Apie\Interfaces\ApiResourceRetrieverInterface;
use W2w\Lib\Apie\Plugins\Core\DataLayers\MemoryDataLayer;
use W2w\Lib\ApieObjectAccessNormalizer\ObjectAccess\ObjectAccessInterface;

class FallbackFactory implements ApiResourceFactoryInterface
{
    private $propertyAccessor;

    private $identifierExtractor;

    public function __construct(
        ObjectAccessInterface $propertyAccessor,
        IdentifierExtractor $identifierExtractor
    ) {
        $this->propertyAccessor = $propertyAccessor;
        $this->identifierExtractor = $identifierExtractor;
    }

    /**
     * Returns true if this factory can create this identifier.
     *
     * @param string $identifier
     * @return bool
     */
    public function hasApiResourceRetrieverInstance(string $identifier): bool
    {
        return  $identifier === MemoryDataLayer::class || $this->isClassWithoutConstructorArguments($identifier);
    }

    /**
     * Gets an instance of ApiResourceRetrieverInstance
     * @param string $identifier
     * @return ApiResourceRetrieverInterface
     */
    public function getApiResourceRetrieverInstance(string $identifier): ApiResourceRetrieverInterface
    {
        switch ($identifier) {
            case MemoryDataLayer::class:
                return new MemoryDataLayer(
                    $this->propertyAccessor,
                    $this->identifierExtractor
                );
        }
        $retriever = $this->createClassWithoutConstructorArguments($identifier);
        if (!$retriever instanceof ApiResourceRetrieverInterface) {
            throw new InvalidClassTypeException($identifier, 'ApiResourceRetrieverInterface');
        }
        return $retriever;
    }

    /**
     * Returns true if this factory can create this identifier.
     *
     * @param string $identifier
     * @return bool
     */
    public function hasApiResourcePersisterInstance(string $identifier): bool
    {
        return $identifier === MemoryDataLayer::class || $this->isClassWithoutConstructorArguments($identifier);
    }

    /**
     * Gets an instance of ApiResourceRetrieverInstance
     * @param string $identifier
     * @return ApiResourcePersisterInterface
     */
    public function getApiResourcePersisterInstance(string $identifier): ApiResourcePersisterInterface
    {
        if ($identifier === MemoryDataLayer::class) {
            return new MemoryDataLayer(
                $this->propertyAccessor,
                $this->identifierExtractor
            );
        }
        $retriever = $this->createClassWithoutConstructorArguments($identifier);
        if (!$retriever instanceof ApiResourcePersisterInterface) {
            throw new InvalidClassTypeException($identifier, 'ApiResourcePersisterInterface');
        }
        return $retriever;
    }

    private function isClassWithoutConstructorArguments(string $identifier): bool
    {
        try {
            $reflClass = new ReflectionClass($identifier);
        } catch (ReflectionException $reflectionException) {
            return false;
        }
        return !$reflClass->getConstructor() || $reflClass->getConstructor()->getNumberOfRequiredParameters() === 0;
    }

    private function createClassWithoutConstructorArguments(string $identifier): object
    {
        try {
            $reflClass = new ReflectionClass($identifier);
        } catch (ReflectionException $reflectionException) {
            throw new CouldNotConstructApiResourceClassException($identifier, $reflectionException);
        }
        if ($reflClass->getConstructor() && $reflClass->getConstructor()->getNumberOfRequiredParameters() > 0) {
            throw new CouldNotConstructApiResourceClassException($identifier);
        }
        return new $identifier();
    }
}
