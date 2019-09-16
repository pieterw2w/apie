<?php
$id = 'test connection check';
$status = 'Error connecting: Mock queue is empty';

return [
    0 => [
        0 => new \W2w\Lib\Apie\ApiResources\Status($id, $status),
        1 => new \W2w\Lib\Apie\ApiResources\Status($id, $status),
    ],
    1 => [
        0 => new \W2w\Lib\Apie\ApiResources\Status($id, $status),
        1 => new \W2w\Lib\Apie\ApiResources\Status($id, $status),
    ],
];
