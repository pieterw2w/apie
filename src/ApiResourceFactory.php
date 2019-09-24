<?php

namespace W2w\Lib\Apie;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
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
     * @var ContainerInterface|null
     */
    private $container;

    /**
     * Stored instantiated retrievers. This makes the method less dynamic and eases testing.
     *
     * @var mixed[]
     */
    private $instantiatedRetrievers = [];

    /**
     * Stored instantiated retrievers. This makes the method less dynamic and eases testing.
     *
     * @var mixed[]
     */
    private $instantiatedPersisters = [];

    /**
     * @param ContainerInterface|null $container
     */
    public function __construct(?ContainerInterface $container = null)
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
        if (empty($this->instantiatedRetrievers[$identifier])) {
            if ($this->container && $this->container->has($identifier)) {
                $retriever = $this->container->get($identifier);
            } else {
                try {
                    $reflClass = new ReflectionClass($identifier);
                } catch (ReflectionException $reflectionException) {
                    throw new CouldNotConstructApiResourceClassException($identifier, $reflectionException);
                }
                if ($reflClass->getConstructor() && $reflClass->getConstructor()->getNumberOfRequiredParameters() > 0) {
                    throw new CouldNotConstructApiResourceClassException($identifier);
                }
                $retriever = new $identifier();
            }

            if (!$retriever instanceof ApiResourceRetrieverInterface) {
                throw new InvalidClassTypeException($identifier, 'ApiResourceRetrieverInterface');
            }
            $this->instantiatedRetrievers[$identifier] = $retriever;
        }
        return $this->instantiatedRetrievers[$identifier];
    }

    /**
     * Creates a Api Resource Persister instance.
     *
     * @param string $identifier
     * @return ApiResourcePersisterInterface
     */
    public function getApiResourcePersisterInstance(string $identifier): ApiResourcePersisterInterface
    {
        if (empty($this->instantiatedPersisters[$identifier])) {
            if ($this->container && $this->container->has($identifier)) {
                $persister = $this->container->get($identifier);
            } else {
                try {
                    $reflClass = new ReflectionClass($identifier);
                } catch (ReflectionException $reflectionException) {
                    throw new CouldNotConstructApiResourceClassException($identifier, $reflectionException);
                }
                if ($reflClass->getConstructor() && $reflClass->getConstructor()->getNumberOfRequiredParameters() > 0) {
                    throw new CouldNotConstructApiResourceClassException($identifier);
                }
                $persister = new $identifier();
            }

            if (!$persister instanceof ApiResourcePersisterInterface) {
                throw new InvalidClassTypeException($identifier, 'ApiResourcePersisterInterface');
            }
            $this->instantiatedPersisters[$identifier] = $persister;
        }

        return $this->instantiatedPersisters[$identifier];
    }
}
