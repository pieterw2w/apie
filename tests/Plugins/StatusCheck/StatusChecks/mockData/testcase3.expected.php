<?php

use W2w\Lib\Apie\Plugins\StatusCheck\ApiResources\Status;

$id = 'test connection check';
$status = 'Unexpected response';

return [
    0 => [
        0 => new Status($id, 'OK', null, ['statuses' => null]),
        1 => new Status($id, 'OK', null, ['statuses' => null]),
    ],
    1 => [
        0 => new Status($id, $status, null, ['statuses' => null]),
        1 => new Status($id, $status, null, ['statuses' => null]),
    ],
];
