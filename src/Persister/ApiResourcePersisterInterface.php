<?php

namespace W2w\Lib\Apie\Persister;

interface ApiResourcePersisterInterface
{
    public function persistNew($resource, array $context = []);

    public function persistExisting($resource, $int, array $context = []);

    public function remove(string $resourceClass, $id, array $context);
}
