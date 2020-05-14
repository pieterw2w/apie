<?php

namespace W2w\Lib\Apie\Core\Models;

use W2w\Lib\Apie\Annotations\ApiResource;
use W2w\Lib\Apie\Exceptions\InvalidReturnTypeOfApiResourceException;
use W2w\Lib\Apie\Interfaces\ApiResourcePersisterInterface;
use W2w\Lib\Apie\Interfaces\ApiResourceRetrieverInterface;

/**
 * Metadata class of an ApiResource. This is a composite of
 * - a class name
 * - an ApiResource annotation
 * - a retriever class instance (optional)
 * - a persister class instance (optional)
 */
class ApiResourceClassMetadata
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var ApiResource
     */
    private $resource;

    /**
     * @var ApiResourceRetrieverInterface|null
     */
    private $resourceRetriever;

    /**
     * @var ApiResourcePersisterInterface|null
     */
    private $resourcePersister;

    /**
     * @param string $className
     * @param ApiResource $resource
     * @param ApiResourceRetrieverInterface|null $resourceRetriever
     * @param ApiResourcePersisterInterface|null $resourcePersister
     */
    public function __construct(
        string $className,
        ApiResource $resource,
        ?ApiResourceRetrieverInterface $resourceRetriever,
        ?ApiResourcePersisterInterface $resourcePersister
    ) {
        $this->className = $className;
        $this->resource = $resource;
        $this->resourceRetriever = $resourceRetriever;
        $this->resourcePersister = $resourcePersister;
    }

    /**
     * Returns true if the Api resource has a retriever instance.
     *
     * @return bool
     */
    public function hasResourceRetriever(): bool
    {
        return !empty($this->resourceRetriever);
    }

    /**
     * Returns the retriever instance.
     *
     * @return ApiResourceRetrieverInterface
     */
    public function getResourceRetriever(): ApiResourceRetrieverInterface
    {
        if (empty($this->resourceRetriever)) {
            throw new InvalidReturnTypeOfApiResourceException(
                null,
                '(null)',
                ApiResourceRetrieverInterface::class
            );
        }
        return $this->resourceRetriever;
    }

    /**
     * Returns true if the Api resource has a persister instance.
     *
     * @return bool
     */
    public function hasResourcePersister(): bool
    {
        return !empty($this->resourcePersister);
    }

    /**
     * Returns the persister instance.
     *
     * @return ApiResourcePersisterInterface
     */
    public function getResourcePersister(): ApiResourcePersisterInterface
    {
        if (empty($this->resourcePersister)) {
            throw new InvalidReturnTypeOfApiResourceException(
                null,
                '(null)',
                ApiResourcePersisterInterface::class
            );
        }
        return $this->resourcePersister;
    }

    /**
     * Returns the context metadata of the instance. This will be sent to the persister and retriever.
     *
     * @return array
     */
    public function getContext(): array
    {
        return $this->resource->context ?? [];
    }

    /**
     * Returns true if GET /resource/ is allowed.
     *
     * @return bool
     */
    public function allowGetAll(): bool
    {
        return !in_array('get', $this->resource->disabledMethods) && !in_array('get-all', $this->resource->disabledMethods);
    }

    /**
     * Returns true if GET /resource/{id} is allowed.
     *
     * @return bool
     */
    public function allowGet(): bool
    {
        return !in_array('get', $this->resource->disabledMethods) && $this->hasResourceRetriever();
    }

    /**
     * Returns true if POST /resource/ is allowed.
     *
     * @return bool
     */
    public function allowPost(): bool
    {
        return !in_array('post', $this->resource->disabledMethods) && $this->hasResourcePersister();
    }

    /**
     * Returns true if DELETE /resource/{id} is allowed.
     *
     * @return bool
     */
    public function allowDelete(): bool
    {
        return !in_array('delete', $this->resource->disabledMethods) && $this->hasResourceRetriever() && $this->hasResourcePersister();
    }

    /**
     * Returns true if PUT /resource/{id} is allowed.
     *
     * @return bool
     */
    public function allowPut(): bool
    {
        return !in_array('put', $this->resource->disabledMethods) && $this->allowGet() && $this->hasResourcePersister();
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }
}
