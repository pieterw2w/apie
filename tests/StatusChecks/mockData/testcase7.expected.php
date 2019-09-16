<?php
$id = 'test connection check';
$status = 'Status can not be determined for test2';

$data = json_decode('[{"id": "test","status":"OK"},{"id":"test2"}]', true);

return [
    0 => [
        0 => new \W2w\Lib\Apie\ApiResources\Status($id, 'OK', null, ['statuses' => null]),
        1 => new \W2w\Lib\Apie\ApiResources\Status($id, 'OK', null, ['statuses' => $data]),
    ],
    1 => [
        0 => new \W2w\Lib\Apie\ApiResources\Status($id, $status, null, ['statuses' => null]),
        1 => new \W2w\Lib\Apie\ApiResources\Status($id, $status, null, ['statuses' => $data]),
    ],
];
