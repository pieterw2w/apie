<?php

namespace W2w\Lib\Apie\Controllers;

use W2w\Lib\Apie\OpenApiSchema\OpenApiSpecGenerator;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Returns the OpenAPI specs as JSON.
 */
class DocsController
{
    /**
     * @var OpenApiSpecGenerator
     */
    private $openApiSpecGenerator;

    /**
     * @param OpenApiSpecGenerator $openApiSpecGenerator
     */
    public function __construct(OpenApiSpecGenerator $openApiSpecGenerator)
    {
        $this->openApiSpecGenerator = $openApiSpecGenerator;
    }

    public function __invoke()
    {
        return new JsonResponse(
            $this->openApiSpecGenerator->getOpenApiSpec()->toArray(),
            200,
            [],
            JSON_UNESCAPED_SLASHES
        );
    }
}
