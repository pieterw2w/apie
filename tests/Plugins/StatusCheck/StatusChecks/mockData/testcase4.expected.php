<?php

use W2w\Lib\Apie\Plugins\StatusCheck\ApiResources\Status;

$id = 'test connection check';
$status = 'Unexpected response';

$data = json_decode('[{"id": "test","status":"OK","no_errors":true},{"id": "test2","status":"OK"}]', true);

return [
    0 => [
        0 => new Status($id, 'OK', null, ['statuses' => null]),
        1 => new Status($id, 'OK', null, ['statuses' => $data]),
    ],
    1 => [
        0 => new Status($id, 'OK', null, ['statuses' => null]),
        1 => new Status($id, 'OK', null, ['statuses' => $data]),
    ],
];
