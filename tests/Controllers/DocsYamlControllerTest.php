<?php
namespace W2w\Test\Apie\Controllers;

use erasys\OpenApi\Spec\v3\Document;
use erasys\OpenApi\Spec\v3\Info;
use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Controllers\DocsYamlController;
use W2w\Lib\Apie\OpenApiSchema\OpenApiSpecGenerator;
use Zend\Diactoros\Response\TextResponse;

class DocsYamlControllerTest extends TestCase
{
    public function testInvoke()
    {
        $document = new Document(
            new Info('Unittest', '1.0', "This spec is generated by a unit test"),
            []
        );

        $specGenerator = $this->prophesize(OpenApiSpecGenerator::class);
        $specGenerator->getOpenApiSpec()
            ->shouldBeCalled()
            ->willReturn($document);

        $item = new DocsYamlController($specGenerator->reveal());

        $actual = $item();
        $this->assertInstanceOf(TextResponse::class, $actual);
        $expected = $document->toYaml();
        $this->assertEquals($expected, (string) $actual->getBody());
    }
}
