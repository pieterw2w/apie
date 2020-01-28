<?php
namespace W2w\Test\Apie\Mocks\ApiResources;

use W2w\Lib\Apie\Annotations\ApiResource;
use W2w\Lib\Apie\Plugins\Core\DataLayers\NullDataLayer;

/**
 * @ApiResource(disabledMethods={"get"}, persistClass=NullDataLayer::class)
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
