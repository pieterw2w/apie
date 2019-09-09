<?php

namespace W2w\Lib\Apie;

use W2w\Lib\Apie\Persister\ApiResourcePersisterInterface;
use W2w\Lib\Apie\Retriever\ApiResourceRetrieverInterface;
use Psr\Container\ContainerInterface;
use RuntimeException;
use UnexpectedValueException;

class ApiResourceFactory implements ApiResourceFactoryInterface
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getApiResourceRetrieverInstance(string $identifier): ApiResourceRetrieverInterface
    {
        if ($this->container->has($identifier)) {
            $retriever = $this->container->get($identifier);
        } else {
            $reflClass = new \ReflectionClass($identifier);
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

    public function getApiResourcePersisterInstance(string $identifier): ApiResourcePersisterInterface
    {
        if ($this->container->has($identifier)) {
            $persister = $this->container->get($identifier);
        } else {
            $reflClass = new \ReflectionClass($identifier);
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
