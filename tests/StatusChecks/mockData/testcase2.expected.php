<?php
$id = 'test connection check';
$status = 'Response error: Unit test not found!';

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
