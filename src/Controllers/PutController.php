<?php

namespace W2w\Lib\Apie\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use W2w\Lib\Apie\ApiResourceFacade;
use W2w\Lib\Apie\ClassResourceConverter;

/**
 * Controller to handle a PUT request.
 */
class PutController
{
    private $converter;

    private $apiResourceFacade;

    /**
     * @param ClassResourceConverter $converter
     * @param ApiResourceFacade $apiResourceFacade
     */
    public function __construct(
        ClassResourceConverter $converter,
        ApiResourceFacade $apiResourceFacade
    ) {
        $this->converter = $converter;
        $this->apiResourceFacade = $apiResourceFacade;
    }

    /**
     * @param ServerRequestInterface $request
     * @param string $resource
     * @param string $id
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, string $resource, string $id): ResponseInterface
    {
        $resourceClass = $this->converter->denormalize($resource);

        return $this->apiResourceFacade->put($resourceClass, $id, $request)->getResponse();
    }
}
