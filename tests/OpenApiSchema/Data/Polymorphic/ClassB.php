<?php


namespace W2w\Test\Apie\OpenApiSchema\Data\Polymorphic;

class ClassB implements TestInterface
{
    /**
     * @var string|null
     */
    public $this_is_b;

    /**
     * @var string
     */
    public $b_or_c;

    private $requiredInInterface = 'initial';

    public function __construct(string $requiredInInterface)
    {
        $this->requiredInInterface = $requiredInInterface;
        return $this;
    }

    public function getType(): string
    {
        return 'B';
    }

    public function getRequiredInInterface(): string
    {
        return $this->requiredInInterface;
    }
}
