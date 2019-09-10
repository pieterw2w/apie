<?php


namespace W2w\Test\Apie\Controllers;

use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\ApiResourceFacade;
use W2w\Lib\Apie\ClassResourceConverter;
use W2w\Lib\Apie\Controllers\DeleteController;
use W2w\Lib\Apie\Models\ApiResourceFacadeResponse;
use Zend\Diactoros\Response\TextResponse;

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
        $actual = $testItem('my-resource', 42);
        $this->assertEquals($response, $actual);
    }
}
