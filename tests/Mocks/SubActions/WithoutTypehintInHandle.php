<?php


namespace W2w\Test\Apie\Mocks\SubActions;


class WithoutTypehintInHandle
{
    /**
     * Calculates md5 of status.
     *
     * @param mixed $status
     * @return string
     */
    public function handle($status): string {
        return md5(json_encode($status));
    }
}
