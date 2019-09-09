<?php

namespace W2w\Lib\Apie\Retriever;

interface ApiResourceRetrieverInterface
{
    public function retrieve(string $resourceClass, $id, array $context);

    public function retrieveAll(string $resourceClass, array $context, int $pageIndex, int $numberOfItems): iterable;
}
