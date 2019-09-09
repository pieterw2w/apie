<?php

namespace W2w\Lib\Apie;

use ReflectionClass;
use W2w\Lib\Apie\Persisters\ApiResourcePersisterInterface;
use W2w\Lib\Apie\Retrievers\ApiResourceRetrieverInterface;
use Psr\Container\ContainerInterface;
use RuntimeException;
use UnexpectedValueException;

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
            if ($reflClass->getConstructor()->getNumberOfRequiredParameters() > 0) {
                throw new UnexpectedValueException(
                    'Class '
                    . $identifier
                    . ' has required constructor arguments and need to be registered as a service in a service provider.'
                );
            }
            $retriever = new $identifier();
        }

        if (!$retriever instanceof ApiResourceRetrieverInterface) {
            throw new RuntimeException(
                $identifier . ' does not implement ApiResourceRetrieverInterface'
            );
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
            if ($reflClass->getConstructor()->getNumberOfRequiredParameters() > 0) {
                throw new UnexpectedValueException(
                    'Class '
                    . $identifier
                    . ' has required constructor arguments and need to be registered as a service in a service provider.'
                );
            }
            $persister = new $identifier();
        }

        if (!$persister instanceof ApiResourcePersisterInterface) {
            throw new RuntimeException(
                $identifier . ' does not implement ApiResourceRetrieverInterface'
            );
        }

        return $persister;
    }
}
