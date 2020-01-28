<?php

namespace W2w\Lib\Apie\Plugins\StatusCheck\StatusChecks;

use W2w\Lib\Apie\Plugins\StatusCheck\ApiResources\Status;

/**
 * Interface for a single status check.
 */
interface StatusCheckInterface
{
    /**
     * Gets current status of the status check.
     *
     * @return Status
     */
    public function getStatus(): Status;
}
