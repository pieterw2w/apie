<?php

namespace W2w\Test\Apie\Features\Pagination;

use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Annotations\ApiResource;
use W2w\Lib\Apie\Apie;
use W2w\Lib\Apie\DefaultApie;
use W2w\Lib\Apie\Plugins\Core\DataLayers\MemoryDataLayer;
use W2w\Lib\Apie\Plugins\FakeAnnotations\FakeAnnotationsPlugin;
use W2w\Lib\Apie\Plugins\FileStorage\DataLayers\FileStorageDataLayer;
use W2w\Lib\Apie\Plugins\FileStorage\FileStoragePlugin;
use W2w\Lib\Apie\Plugins\Pagination\PaginationPlugin;
use W2w\Lib\Apie\Plugins\StaticConfig\StaticResourcesPlugin;
use W2w\Test\Apie\Mocks\ApiResources\SimplePopo;

class PaginationTest extends TestCase
{
    private $subfolder;

    protected function setUp(): void
    {
        $this->subfolder = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('PaginationTest');
        mkdir($this->subfolder);
    }

    protected function tearDown(): void
    {
        if ($this->subfolder && $this->subfolder !== DIRECTORY_SEPARATOR) {
            system('rm -rf ' . escapeshellarg($this->subfolder));
        }
    }

    private function createApie(string $dataLayerClass): Apie
    {
        $plugins = [
            new StaticResourcesPlugin(
                [
                    SimplePopo::class
                ]
            ),
            new FakeAnnotationsPlugin([
                SimplePopo::class => ApiResource::createFromArray([
                    'retrieveClass' => $dataLayerClass,
                    'persistClass' => $dataLayerClass,
                ])
            ]),
            new FileStoragePlugin($this->subfolder . DIRECTORY_SEPARATOR . 'file-storage')
        ];
        $apie = DefaultApie::createDefaultApie(false, $plugins, $this->subfolder);
        srand(0);
        $facade = $apie->getApiResourceFacade();
        $request = new ServerRequest(
            'POST',
            '/simple_popo',
            [
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ],
            '{}'
        );
        for ($i = 0; $i < 510; $i++) {
            $facade->post(SimplePopo::class, $request);
        }
        return $apie;
    }

    /**
     * @dataProvider provideDataLayerClasses
     */
    public function testFirstPage(string $dataLayerClass)
    {
        $request = new ServerRequest(
            'GET',
            '/simple_popo',
            ['accept' => 'application/json']
        );
        $apie = $this->createApie($dataLayerClass);
        $facadeResponse = $apie->getApiResourceFacade()->getAll(SimplePopo::class, $request);
        $this->assertCount(20, $facadeResponse->getNormalizedData());
        $response = $facadeResponse->getResponse();
        $expected = [
            PaginationPlugin::COUNT_HEADER => ['510'],
            PaginationPlugin::FIRST_HEADER => ['/simple_popo?page=0&limit=20'],
            PaginationPlugin::LAST_HEADER => ['/simple_popo?page=25&limit=20'],
            PaginationPlugin::NEXT_HEADER => ['/simple_popo?page=1&limit=20'],
            'content-type' => ['application/json'],
        ];

        $this->assertEquals($expected, $response->getHeaders());
    }

    /**
     * @dataProvider provideDataLayerClasses
     */
    public function testMiddlePage(string $dataLayerClass)
    {
        $request = (new ServerRequest(
            'GET',
            '/simple_popo',
            ['accept' => 'application/json']
        ))->withQueryParams(['page' => 10, 'limit' => 15]);
        $apie = $this->createApie($dataLayerClass);
        $facadeResponse = $apie->getApiResourceFacade()->getAll(SimplePopo::class, $request);
        $response = $facadeResponse->getResponse();

        $expected = [
            PaginationPlugin::COUNT_HEADER => ['510'],
            PaginationPlugin::FIRST_HEADER => ['/simple_popo?page=0&limit=15'],
            PaginationPlugin::LAST_HEADER => ['/simple_popo?page=33&limit=15'],
            PaginationPlugin::NEXT_HEADER => ['/simple_popo?page=11&limit=15'],
            PaginationPlugin::PREV_HEADER => ['/simple_popo?page=9&limit=15'],
            'content-type' => ['application/json'],
        ];

        $this->assertEquals($expected, $response->getHeaders());

        $this->assertCount(15, $facadeResponse->getNormalizedData());
    }

    /**
     * @dataProvider provideDataLayerClasses
     */
    public function testLastPage(string $dataLayerClass)
    {
        $request = (new ServerRequest(
            'GET',
            '/simple_popo',
            ['accept' => 'application/json']
        ))->withQueryParams(['page' => 25]);
        $apie = $this->createApie($dataLayerClass);
        $facadeResponse = $apie->getApiResourceFacade()->getAll(SimplePopo::class, $request);
        $response = $facadeResponse->getResponse();

        $expected = [
            PaginationPlugin::COUNT_HEADER => ['510'],
            PaginationPlugin::FIRST_HEADER => ['/simple_popo?page=0&limit=20'],
            PaginationPlugin::LAST_HEADER => ['/simple_popo?page=25&limit=20'],
            PaginationPlugin::PREV_HEADER => ['/simple_popo?page=24&limit=20'],
            'content-type' => ['application/json'],
        ];

        $this->assertEquals($expected, $response->getHeaders());

        $this->assertCount(10, $facadeResponse->getNormalizedData());
    }

    /**
     * @dataProvider provideDataLayerClasses
     */
    public function testOutOfRangePage(string $dataLayerClass)
    {
        $request = (new ServerRequest(
            'GET',
            '/simple_popo',
            ['accept' => 'application/json']
        ))->withQueryParams(['page' => 9001]);
        $apie = $this->createApie($dataLayerClass);
        $facadeResponse = $apie->getApiResourceFacade()->getAll(SimplePopo::class, $request);
        $response = $facadeResponse->getResponse();

        $expected = [
            PaginationPlugin::COUNT_HEADER => ['510'],
            PaginationPlugin::FIRST_HEADER => ['/simple_popo?page=0&limit=20'],
            PaginationPlugin::LAST_HEADER => ['/simple_popo?page=25&limit=20'],
            PaginationPlugin::PREV_HEADER => ['/simple_popo?page=9000&limit=20'],
            'content-type' => ['application/json'],
        ];

        $this->assertEquals($expected, $response->getHeaders());

        $this->assertCount(0, $facadeResponse->getNormalizedData());
    }

    public function provideDataLayerClasses()
    {
        yield [FileStorageDataLayer::class];
        yield [MemoryDataLayer::class];
    }
}
