<?php

namespace W2w\Lib\Apie\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use W2w\Lib\Apie\ApiResourceFacade;
use W2w\Lib\Apie\ClassResourceConverter;
use W2w\Lib\Apie\Exceptions\InvalidPageLimitException;
use W2w\Lib\Apie\Exceptions\PageIndexShouldNotBeNegativeException;

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
     * @param string $resource
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $psrRequest, string $resource): ResponseInterface
    {
        $resourceClass = $this->converter->denormalize($resource);
        $params = $psrRequest->getQueryParams();
        $pageIndex = (int) ($params['page'] ?? 0);
        $limit = (int) ($params['limit'] ?? 20);

        if ($pageIndex < 0) {
            throw new PageIndexShouldNotBeNegativeException();
        }
        if ($limit < 1) {
            throw new InvalidPageLimitException();
        }

        return $this->apiResourceFacade->getAll(
            $resourceClass,
            $pageIndex,
            $limit,
            $psrRequest
        )->getResponse();
    }
}
