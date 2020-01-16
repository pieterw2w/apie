<?php


namespace W2w\Test\Apie\OpenApiSchema\Data\Polymorphic;

class ClassC implements TestInterface
{
    /**
     * @var int
     */
    public $b_or_c;

    public function getType(): string
    {
        return 'C';
    }

    public function getRequiredInInterface(): string
    {
        return 'this value never changes';
    }
}
