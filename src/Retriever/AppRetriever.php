<?php

namespace W2w\Lib\Apie\Retriever;

use App\ApiResources\App;
use Illuminate\Contracts\Config\Repository;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AppRetriever implements ApiResourceRetrieverInterface
{
    private $config;

    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    public function retrieve(string $resourceClass, $id, array $context)
    {
        if ($id !== 'name') {
            throw new HttpException(404, 'Identifier should be "name"');
        }

        return new App(
            $this->config->get('app.name'),
            $this->config->get('app.env'),
            trim(`git rev-parse HEAD`),
            $this->config->get('app.debug')
        );
    }

    public function retrieveAll(string $resourceClass, array $context, int $pageIndex, int $numberOfItems): iterable
    {
        if ($pageIndex > 0) {
            return [];
        }

        return [$this->retrieve($resourceClass, 'name', $context)];
    }
}
