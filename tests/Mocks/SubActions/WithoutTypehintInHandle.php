<?php


namespace W2w\Test\Apie\Mocks\SubActions;


class WithoutTypehintInHandle
{
    public function handle($status): string {
        return md5(json_encode($status));
    }
}
