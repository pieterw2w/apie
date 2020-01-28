<?php


namespace W2w\Test\Apie\Controllers;

use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Controllers\DeleteController;
use W2w\Lib\Apie\Core\ApiResourceFacade;
use W2w\Lib\Apie\Core\ClassResourceConverter;
use W2w\Lib\Apie\Core\Models\ApiResourceFacadeResponse;
use Zend\Diactoros\Response\TextResponse;
use Zend\Diactoros\ServerRequest;

class DeleteControllerTest extends TestCase
{
    public function testInvoke()
    {
        $response = new TextResponse('', 204);

        $facadeResponse = $this->prophesize(ApiResourceFacadeResponse::class);
        $facadeResponse->getResponse()
            ->shouldBeCalled()
            ->willReturn($response);

        $apiResourceFacade = $this->prophesize(ApiResourceFacade::class);
        $apiResourceFacade->delete(__CLASS__, 42)
            ->shouldBeCalled()
            ->willReturn($facadeResponse->reveal());

        $classResourceConverter = $this->prophesize(ClassResourceConverter::class);
        $classResourceConverter->denormalize('my-resource')
            ->shouldBeCalled()
            ->willReturn(__CLASS__);
        $testItem = new DeleteController($apiResourceFacade->reveal(), $classResourceConverter->reveal());

        $psrRequest = (new ServerRequest())
            ->withAttribute('resource', 'my-resource')
            ->withAttribute('id', 42);

        $actual = $testItem($psrRequest);
        $this->assertEquals($response, $actual);
    }
}
