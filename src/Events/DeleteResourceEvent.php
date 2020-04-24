<?php


namespace W2w\Lib\Apie\Events;


class DeleteResourceEvent
{
    /**
     * @var string
     */
    private $resourceClass;

    /**
     * @var string
     */
    private $id;

    public function __construct(string $resourceClass, string $id)
    {
        $this->resourceClass = $resourceClass;
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getResourceClass(): string
    {
        return $this->resourceClass;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
}
