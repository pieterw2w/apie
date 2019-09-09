<?php

namespace W2w\Lib\Apie\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
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
            throw new HttpException(422, 'Page index should not be negative!');
        }
        if ($limit < 1) {
            throw new HttpException(422, 'Page limit should not be lower than 1!');
        }

        return $this->apiResourceFacade->getAll(
            $resourceClass,
            $pageIndex,
            $limit,
            $psrRequest
        )->getResponse();
    }
}
