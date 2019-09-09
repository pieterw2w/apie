<?php

namespace W2w\Lib\Apie\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use W2w\Lib\Apie\ApiResourceFacade;
use W2w\Lib\Apie\ClassResourceConverter;

/**
 * Controller that handles to get a single resource.
 */
class GetController
{
    /**
     * @var ApiResourceFacade
     */
    private $apiResourceFacade;

    /**
     * @var ClassResourceConverter
     */
    private $converter;

    /**
     * @param ApiResourceFacade $apiResourceFacade
     * @param ClassResourceConverter $converter
     */
    public function __construct(
        ApiResourceFacade $apiResourceFacade,
        ClassResourceConverter $converter
    ) {
        $this->apiResourceFacade = $apiResourceFacade;
        $this->converter = $converter;
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

        return $this->apiResourceFacade->get($resourceClass, $id, $request)->getResponse();
    }
}
