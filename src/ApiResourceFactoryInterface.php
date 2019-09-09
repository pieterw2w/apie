<?php

namespace W2w\Lib\Apie;

use W2w\Lib\Apie\Persister\ApiResourcePersisterInterface;
use W2w\Lib\Apie\Retriever\ApiResourceRetrieverInterface;

interface ApiResourceFactoryInterface
{
    public function getApiResourceRetrieverInstance(string $identifier): ApiResourceRetrieverInterface;

    public function getApiResourcePersisterInstance(string $identifier): ApiResourcePersisterInterface;
}
