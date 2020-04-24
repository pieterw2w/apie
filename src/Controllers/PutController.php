<?php

namespace W2w\Lib\Apie\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use W2w\Lib\Apie\Core\ApiResourceFacade;
use W2w\Lib\Apie\Core\ClassResourceConverter;

/**
 * Controller to handle a PUT request.
 */
class PutController
{
    /**
     * @var ClassResourceConverter
     */
    private $converter;

    /**
     * @var ApiResourceFacade
     */
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
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $resource = $request->getAttribute('resource') ?? '';
        $id = $request->getAttribute('id') ?? '';
        $resourceClass = $this->converter->denormalize($resource);

        return $this->apiResourceFacade->put($resourceClass, $id, $request)->getResponse();
    }
}
