<?php
namespace W2w\Test\Apie\Controllers;

use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Controllers\GetAllController;
use W2w\Lib\Apie\Core\ApiResourceFacade;
use W2w\Lib\Apie\Core\ClassResourceConverter;
use W2w\Lib\Apie\Core\Models\ApiResourceFacadeResponse;
use Zend\Diactoros\Response\TextResponse;
use Zend\Diactoros\ServerRequest;

class GetAllControllerTest extends TestCase
{
    public function testInvoke()
    {
        $psrRequest = (new ServerRequest())
            ->withQueryParams(['page' => 1, 'limit' => 10])
            ->withAttribute('resource', 'my-resource');

        $response = new TextResponse('[]', 200);

        $facadeResponse = $this->prophesize(ApiResourceFacadeResponse::class);
        $facadeResponse->getResponse()
            ->shouldBeCalled()
            ->willReturn($response);

        $apiResourceFacade = $this->prophesize(ApiResourceFacade::class);
        $apiResourceFacade->getAll(__CLASS__, $psrRequest)
            ->shouldBeCalled()
            ->willReturn($facadeResponse->reveal());

        $classResourceConverter = $this->prophesize(ClassResourceConverter::class);
        $classResourceConverter->denormalize('my-resource')
            ->shouldBeCalled()
            ->willReturn(__CLASS__);
        $testItem = new GetAllController($apiResourceFacade->reveal(), $classResourceConverter->reveal());
        $actual = $testItem($psrRequest);
        $this->assertEquals($response, $actual);
    }
}
