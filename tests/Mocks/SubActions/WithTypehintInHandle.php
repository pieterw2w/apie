<?php


namespace W2w\Test\Apie\Mocks\SubActions;

use ReflectionClass;
use W2w\Lib\Apie\Plugins\StatusCheck\ApiResources\Status;

class WithTypehintInHandle
{
    public function handle(Status $status): ReflectionClass {
        return new ReflectionClass($status);
    }
}
