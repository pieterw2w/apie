<?php


namespace W2w\Test\Apie\Mocks\Data;

use W2w\Lib\Apie\Annotations\ApiResource;
use W2w\Lib\Apie\Persisters\NullPersister;

/**
 * @ApiResource(disabledMethods={"get"}, persistClass=NullPersister::class)
 */
class SumExample
{
    private $one;

    private $two;

    public function __construct(float $one, float $two)
    {
        $this->one = $one;
        $this->two = $two;
    }

    public function getAddition(): float
    {
        return $this->one + $this->two;
    }
}
