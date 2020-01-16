<?php


namespace W2w\Test\Apie\OpenApiSchema\Data\Polymorphic;

class ClassA implements TestInterface
{
    /**
     * @var string
     */
    public $this_is_a;

    private $requiredInInterface = 'initial';

    public function setRequiredInInterface(string $value): self
    {
        $this->requiredInInterface = $value;
        return $this;
    }

    public function getType(): string
    {
        return 'a';
    }

    public function getRequiredInInterface(): string
    {
        return $this->requiredInInterface;
    }
}
