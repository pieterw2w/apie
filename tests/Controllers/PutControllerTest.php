<?php

namespace W2w\Test\Apie\Controllers;

use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\ApiResourceFacade;
use W2w\Lib\Apie\ClassResourceConverter;
use W2w\Lib\Apie\Controllers\PutController;
use W2w\Lib\Apie\Models\ApiResourceFacadeResponse;
use Zend\Diactoros\Response\TextResponse;
use Zend\Diactoros\ServerRequest;

class PutControllerTest extends TestCase
{
    public function testInvoke()
    {
        $request = new ServerRequest();
        $response = new TextResponse('{"id":42}', 200);

        $facadeResponse = $this->prophesize(ApiResourceFacadeResponse::class);
        $facadeResponse->getResponse()
            ->shouldBeCalled()
            ->willReturn($response);

        $apiResourceFacade = $this->prophesize(ApiResourceFacade::class);
        $apiResourceFacade->put(__CLASS__, 42, $request)
            ->shouldBeCalled()
            ->willReturn($facadeResponse->reveal());

        $classResourceConverter = $this->prophesize(ClassResourceConverter::class);
        $classResourceConverter->denormalize('my-resource')
            ->shouldBeCalled()
            ->willReturn(__CLASS__);
        $testItem = new PutController($classResourceConverter->reveal(), $apiResourceFacade->reveal());
        $actual = $testItem($request, 'my-resource', 42);
        $this->assertEquals($response, $actual);
    }
}
