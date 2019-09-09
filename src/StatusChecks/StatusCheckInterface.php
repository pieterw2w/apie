<?php

namespace W2w\Lib\Apie\StatusChecks;

use W2w\Lib\Apie\ApiResources\Status;

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
