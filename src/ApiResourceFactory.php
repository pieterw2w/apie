<?php

namespace W2w\Lib\Apie;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use W2w\Lib\Apie\Exceptions\CouldNotConstructApiResourceClassException;
use W2w\Lib\Apie\Exceptions\InvalidClassTypeException;
use W2w\Lib\Apie\Persisters\ApiResourcePersisterInterface;
use W2w\Lib\Apie\Retrievers\ApiResourceRetrieverInterface;

/**
 * Creates instances of ApiResourcePersisterInterface and ApiResourceRetrieverInterface.
 */
class ApiResourceFactory implements ApiResourceFactoryInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Creates a Api Resource Retriever instance.
     *
     * @param string $identifier
     * @return ApiResourceRetrieverInterface
     */
    public function getApiResourceRetrieverInstance(string $identifier): ApiResourceRetrieverInterface
    {
        if ($this->container->has($identifier)) {
            $retriever = $this->container->get($identifier);
        } else {
            $reflClass = new ReflectionClass($identifier);
            if ($reflClass->getConstructor() && $reflClass->getConstructor()->getNumberOfRequiredParameters() > 0) {
                throw new CouldNotConstructApiResourceClassException($identifier);
            }
            $retriever = new $identifier();
        }

        if (!$retriever instanceof ApiResourceRetrieverInterface) {
            throw new InvalidClassTypeException($identifier, 'ApiResourceRetrieverInterface');
        }

        return $retriever;
    }

    /**
     * Creates a Api Resource Persister instance.
     *
     * @param string $identifier
     * @return ApiResourcePersisterInterface
     */
    public function getApiResourcePersisterInstance(string $identifier): ApiResourcePersisterInterface
    {
        if ($this->container->has($identifier)) {
            $persister = $this->container->get($identifier);
        } else {
            $reflClass = new ReflectionClass($identifier);
            if ($reflClass->getConstructor() && $reflClass->getConstructor()->getNumberOfRequiredParameters() > 0) {
                throw new CouldNotConstructApiResourceClassException($identifier);
            }
            $persister = new $identifier();
        }

        if (!$persister instanceof ApiResourcePersisterInterface) {
            throw new InvalidClassTypeException($identifier, 'ApiResourcePersisterInterface');
        }

        return $persister;
    }
}
