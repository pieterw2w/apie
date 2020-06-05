<?php

namespace W2w\Lib\Apie\Controllers;

use W2w\Lib\Apie\OpenApiSchema\OpenApiSpecGenerator;
use Zend\Diactoros\Response\TextResponse;

/**
 * Returns the OpenAPI specs as YAML.
 */
class DocsYamlController
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
        return new TextResponse(
            $this->openApiSpecGenerator->getOpenApiSpec()->toYaml(),
            200,
            [
                'Content-Type' => 'application/vnd.oai.openapi+yaml',
            ]
        );
    }
}
