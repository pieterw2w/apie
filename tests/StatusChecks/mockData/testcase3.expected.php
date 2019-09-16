<?php
$id = 'test connection check';
$status = 'Unexpected response';

return [
    0 => [
        0 => new \W2w\Lib\Apie\ApiResources\Status($id, 'OK', null, ['statuses' => null]),
        1 => new \W2w\Lib\Apie\ApiResources\Status($id, 'OK', null, ['statuses' => null]),
    ],
    1 => [
        0 => new \W2w\Lib\Apie\ApiResources\Status($id, $status, null, ['statuses' => null]),
        1 => new \W2w\Lib\Apie\ApiResources\Status($id, $status, null, ['statuses' => null]),
    ],
];
