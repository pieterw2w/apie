<?php

namespace W2w\Lib\Apie\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use W2w\Lib\Apie\ApiResourceFacade;
use W2w\Lib\Apie\ClassResourceConverter;

/**
 * Controller that handles the call to get all resources.
 */
class GetAllController
{
    private $apiResourceFacade;

    private $converter;

    public function __construct(
        ApiResourceFacade $apiResourceFacade,
        ClassResourceConverter $converter
    ) {
        $this->apiResourceFacade = $apiResourceFacade;
        $this->converter = $converter;
    }

    /**
     * @param ServerRequestInterface $psrRequest
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $psrRequest): ResponseInterface
    {
        $resource = $psrRequest->getAttribute('resource') ?? '';
        $resourceClass = $this->converter->denormalize($resource);

        return $this->apiResourceFacade->getAll(
            $resourceClass,
            $psrRequest
        )->getResponse();
    }
}
