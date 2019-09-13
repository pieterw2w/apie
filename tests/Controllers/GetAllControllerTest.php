<?php
namespace W2w\Test\Apie\Controllers;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use W2w\Lib\Apie\ApiResourceFacade;
use W2w\Lib\Apie\ClassResourceConverter;
use W2w\Lib\Apie\Controllers\GetAllController;
use W2w\Lib\Apie\Exceptions\InvalidPageLimitException;
use W2w\Lib\Apie\Exceptions\PageIndexShouldNotBeNegativeException;
use W2w\Lib\Apie\Models\ApiResourceFacadeResponse;
use Zend\Diactoros\Response\TextResponse;
use Zend\Diactoros\ServerRequest;

class GetAllControllerTest extends TestCase
{
    public function testInvoke()
    {
        $psrRequest = (new ServerRequest())
            ->withQueryParams(['page' => 1, 'limit' => 10]);

        $response = new TextResponse('[]', 200);

        $facadeResponse = $this->prophesize(ApiResourceFacadeResponse::class);
        $facadeResponse->getResponse()
            ->shouldBeCalled()
            ->willReturn($response);

        $apiResourceFacade = $this->prophesize(ApiResourceFacade::class);
        $apiResourceFacade->getAll(__CLASS__, 1, 10, $psrRequest)
            ->shouldBeCalled()
            ->willReturn($facadeResponse->reveal());

        $classResourceConverter = $this->prophesize(ClassResourceConverter::class);
        $classResourceConverter->denormalize('my-resource')
            ->shouldBeCalled()
            ->willReturn(__CLASS__);
        $testItem = new GetAllController($apiResourceFacade->reveal(), $classResourceConverter->reveal());
        $actual = $testItem($psrRequest, 'my-resource');
        $this->assertEquals($response, $actual);
    }

    /**
     * @dataProvider  invalidQueryParamsProvider
     */
    public function testInvoke_invalid_query_params(string $expectedExceptionClass, array $queryParams)
    {
        $psrRequest = (new ServerRequest())
            ->withQueryParams($queryParams);

        $apiResourceFacade = $this->prophesize(ApiResourceFacade::class);
        $apiResourceFacade->getAll(Argument::cetera())
                          ->shouldNotBeCalled();

        $classResourceConverter = $this->prophesize(ClassResourceConverter::class);
        $classResourceConverter->denormalize('my-resource')
                               ->shouldBeCalled()
                               ->willReturn(__CLASS__);
        $testItem = new GetAllController($apiResourceFacade->reveal(), $classResourceConverter->reveal());
        $this->expectException($expectedExceptionClass);
        $testItem($psrRequest, 'my-resource');
    }

    public function invalidQueryParamsProvider()
    {
        yield [PageIndexShouldNotBeNegativeException::class, ['page' => -1, 'limit' => 10]];
        yield [InvalidPageLimitException::class, ['page' => 1, 'limit' => 0]];
        yield [PageIndexShouldNotBeNegativeException::class, ['page' => -1, 'limit' => 0]];
    }
}
