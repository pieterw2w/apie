<?php


namespace W2w\Test\Apie\Mocks\SubActions;

use W2w\Test\Apie\Features\AnotherSimplePopo;
use W2w\Test\Apie\Mocks\ApiResources\SumExample;

class WithAdditionalArguments
{
    public function handle(AnotherSimplePopo $status, int $one, int $two): SumExample {
        return new SumExample((int) $status->getId() + $one, $two);
    }
}
