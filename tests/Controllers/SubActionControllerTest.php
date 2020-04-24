<?php

namespace W2w\Test\Apie\Controllers;

use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Controllers\SubActionController;
use W2w\Lib\Apie\Core\ApiResourceFacade;
use W2w\Lib\Apie\Core\ClassResourceConverter;
use W2w\Lib\Apie\Core\Models\ApiResourceFacadeResponse;
use Zend\Diactoros\Response\TextResponse;
use Zend\Diactoros\ServerRequest;

class SubActionControllerTest extends TestCase
{
    public function testInvoke()
    {
        $request = (new ServerRequest())
            ->withAttribute('resource', 'my-resource')
            ->withAttribute('id', 42)
            ->withAttribute('subaction', 'sub-action');
        $response = new TextResponse('{"id":42}', 200);

        $facadeResponse = $this->prophesize(ApiResourceFacadeResponse::class);
        $facadeResponse->getResponse()
            ->shouldBeCalled()
            ->willReturn($response);

        $apiResourceFacade = $this->prophesize(ApiResourceFacade::class);
        $apiResourceFacade->postSubAction(__CLASS__, 42, 'sub-action', $request)
            ->shouldBeCalled()
            ->willReturn($facadeResponse->reveal());

        $classResourceConverter = $this->prophesize(ClassResourceConverter::class);
        $classResourceConverter->denormalize('my-resource')
            ->shouldBeCalled()
            ->willReturn(__CLASS__);
        $testItem = new SubActionController($apiResourceFacade->reveal(), $classResourceConverter->reveal());
        $actual = $testItem($request);
        $this->assertEquals($response, $actual);
    }
}
