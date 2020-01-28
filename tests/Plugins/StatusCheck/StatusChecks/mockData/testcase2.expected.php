<?php

use W2w\Lib\Apie\Plugins\StatusCheck\ApiResources\Status;

$id = 'test connection check';
$status = 'Response error: Unit test not found!';

return [
    0 => [
        0 => new Status($id, $status),
        1 => new Status($id, $status),
    ],
    1 => [
        0 => new Status($id, $status),
        1 => new Status($id, $status),
    ],
];
