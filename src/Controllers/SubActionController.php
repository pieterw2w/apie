<?php

namespace W2w\Lib\Apie\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use W2w\Lib\Apie\Core\ApiResourceFacade;
use W2w\Lib\Apie\Core\ClassResourceConverter;

/**
 * Controller for calling a sub-action.
 */
class SubActionController
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
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request)
    {
        $resource = $request->getAttribute('resource') ?? '';
        $id = $request->getAttribute('id');
        $subAction = $request->getAttribute('subaction');
        $resourceClass = $this->converter->denormalize($resource);
        return $this->apiResourceFacade->postSubAction($resourceClass, $id, $subAction, $request)->getResponse();
    }

}
