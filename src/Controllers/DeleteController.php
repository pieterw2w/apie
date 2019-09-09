<?php

namespace W2w\Lib\Apie\Controllers;

use W2w\Lib\Apie\ApiResourceFacade;
use W2w\Lib\Apie\ClassResourceConverter;
use Psr\Http\Message\ResponseInterface;

/**
 * Controller that handles the DELETE method.
 */
class DeleteController
{
    /**
     * @var ApiResourceFacade
     */
    private $apiResourceFacade;

    /**
     * @var ClassResourceConverter
     */
    private $converter;

    public function __construct(
        ApiResourceFacade $apiResourceFacade,
        ClassResourceConverter $converter
    ) {
        $this->apiResourceFacade = $apiResourceFacade;
        $this->converter = $converter;
    }

    /**
     * @param string $resource
     * @param string $id
     * @return ResponseInterface
     */
    public function __invoke(string $resource, string $id): ResponseInterface
    {
        $resourceClass = $this->converter->denormalize($resource);

        return $this->apiResourceFacade->delete($resourceClass, $id)->getResponse();
    }
}
