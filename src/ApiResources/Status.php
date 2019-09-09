<?php

namespace W2w\Lib\Apie\ApiResources;

use W2w\Lib\Apie\Annotations\ApiResource;
use W2w\Lib\Apie\Retrievers\StatusCheckRetriever;

/**
 * Creates a status api resource. It's best practice to have an end point to do a health check for your REST API.
 *
 * @ApiResource(
 *     retrieveClass=StatusCheckRetriever::class
 * )
 */
class Status
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string|null
     */
    private $optionalReference;

    /**
     * @var array|null
     */
    private $context;

    public function __construct(string $id, string $status = 'OK', ?string $optionalReference = null, ?array $context = null)
    {
        $this->id = $id;
        $this->status = $status;
        $this->optionalReference = $optionalReference;
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Returns some status string. The string 'OK' assumes no error was there.
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Return some reference to some arbitrary URL. Useful to link people to a maintenance page with information
     * how long it takes before it's operational again.
     *
     * @return string|null
     */
    public function getOptionalReference(): ?string
    {
        return $this->optionalReference;
    }

    /**
     * Returns an array with arbitrary data. Can be anything....
     *
     * @return array|null
     */
    public function getContext(): ?array
    {
        return $this->context;
    }

    /**
     * Returns true to tell the status check is 'healthy'.
     *
     * @return bool
     */
    public function hasNoErrors(): bool
    {
        return $this->status === 'OK';
    }
}
