<?php
namespace W2w\Test\Apie\OpenApiSchema\Data\Polymorphic;

class TestObject
{
    /** @var TestInterface */
    public $item;

    /** @var TestInterface[] */
    public $list;
}
