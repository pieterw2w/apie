<?php


namespace W2w\Test\Apie\OpenApiSchema\Data;

class RecursiveObject
{
    /**
     * @var RecursiveObject
     */
    private $child;

    public function setChild(?RecursiveObject $child): self
    {
        $this->child = $child;
        return $this;
    }

    public function getChild(): ?RecursiveObject
    {
        return $this->child;
    }
}
