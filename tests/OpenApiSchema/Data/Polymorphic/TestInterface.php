<?php


namespace W2w\Test\Apie\OpenApiSchema\Data\Polymorphic;


interface TestInterface
{
    public function getType(): string;

    public function getRequiredInInterface(): string;
}
