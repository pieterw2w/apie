<?php

namespace W2w\Lib\Apie\StatusChecks;

use W2w\Lib\Apie\ApiResources\Status;

/**
 * A status check class that can be used for a statically created Status object.
 */
class StaticStatusCheck implements StatusCheckInterface
{
    private $status;

    public function __construct(Status $status)
    {
        $this->status = $status;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }
}
