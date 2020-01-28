<?php
use W2w\Lib\Apie\Plugins\StatusCheck\ApiResources\Status;

$id = 'test connection check';
$status = 'Missing id for status #1';

$data = json_decode('[{"id": "test","status":"OK"},{}]', true);

return [
    0 => [
        0 => new Status($id, 'OK', null, ['statuses' => null]),
        1 => new Status($id, 'OK', null, ['statuses' => $data]),
    ],
    1 => [
        0 => new Status($id, $status, null, ['statuses' => null]),
        1 => new Status($id, $status, null, ['statuses' => $data]),
    ],
];
