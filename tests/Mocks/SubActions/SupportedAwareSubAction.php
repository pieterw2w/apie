<?php


namespace W2w\Test\Apie\Mocks\SubActions;

use W2w\Lib\Apie\Interfaces\SupportedAwareSubActionInterface;
use W2w\Test\Apie\Mocks\ApiResources\FullRestObject;

class SupportedAwareSubAction implements SupportedAwareSubActionInterface
{
    public function isSupported(string $resourceClass): bool
    {
        return $resourceClass === FullRestObject::class;
    }

    public function handle($status): string {
        return md5(json_encode($status->getUuid()));
    }
}
