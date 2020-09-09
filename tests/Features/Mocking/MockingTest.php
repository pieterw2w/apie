<?php

namespace W2w\Test\Apie\Features\Mocking;

use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Annotations\ApiResource;
use W2w\Lib\Apie\Apie;
use W2w\Lib\Apie\Core\SearchFilters\SearchFilterRequest;
use W2w\Lib\Apie\DefaultApie;
use W2w\Lib\Apie\Exceptions\ResourceNotFoundException;
use W2w\Lib\Apie\Plugins\Core\CorePlugin;
use W2w\Lib\Apie\Plugins\Core\DataLayers\MemoryDataLayer;
use W2w\Lib\Apie\Plugins\FakeAnnotations\FakeAnnotationsPlugin;
use W2w\Lib\Apie\Plugins\FileStorage\DataLayers\FileStorageDataLayer;
use W2w\Lib\Apie\Plugins\FileStorage\FileStoragePlugin;
use W2w\Lib\Apie\Plugins\Mock\DataLayers\MockApiResourceDataLayer;
use W2w\Lib\Apie\Plugins\Mock\MockPlugin;
use W2w\Lib\Apie\Plugins\StaticConfig\StaticResourcesPlugin;
use W2w\Test\Apie\Mocks\ApiResources\SimplePopo;

class MockingTest extends TestCase
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
            new MockPlugin([MemoryDataLayer::class]),
            new FileStoragePlugin($this->subfolder . DIRECTORY_SEPARATOR . 'file-storage')
        ];
        return DefaultApie::createDefaultApie(false, $plugins, $this->subfolder);
    }

    public function testMockingTheDataLayerWorks()
    {
        $apie = $this->createApie(FileStorageDataLayer::class);
        $facade = $apie->getApiResourceFacade();
        $postRequest = new ServerRequest(
            'POST',
            '/simple_popo',
            [
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ],
            '{"arbitrary_field":"any"}'
        );
        srand(0);
        $response = $facade->post(SimplePopo::class, $postRequest);
        $actual = $response->getNormalizedData();
        $this->assertEquals('QPBQZRDZRRZYQVKA', $actual['id']);

        /** @var MockApiResourceDataLayer $mockedDataLayer */
        $mockedDataLayer = $apie
            ->getPlugin(MockPlugin::class)
            ->getApiResourceFactory()
            ->getApiResourceRetrieverInstance(FileStorageDataLayer::class);
        $actual = $mockedDataLayer->retrieve(SimplePopo::class, 'QPBQZRDZRRZYQVKA', []);
        $this->assertInstanceOf(SimplePopo::class, $actual);
        $this->assertEquals('any', $actual->arbitraryField);

        /** @var FileStorageDataLayer $dataLayer */
        $dataLayer = $apie
            ->getPlugin(FileStoragePlugin::class)
            ->getApiResourceFactory()
            ->getApiResourceRetrieverInstance(FileStorageDataLayer::class);
        $this->expectException(ResourceNotFoundException::class);
        $dataLayer->retrieve(SimplePopo::class, 'QPBQZRDZRRZYQVKA', []);
    }

    public function testIgnoreListWorks()
    {
        $apie = $this->createApie(MemoryDataLayer::class);
        $facade = $apie->getApiResourceFacade();
        $postRequest = new ServerRequest(
            'POST',
            '/simple_popo',
            [
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ],
            '{"arbitrary_field":"any"}'
        );
        srand(0);
        $response = $facade->post(SimplePopo::class, $postRequest);
        $actual = $response->getNormalizedData();
        $this->assertEquals('QPBQZRDZRRZYQVKA', $actual['id']);

        /** @var MemoryDataLayer $dataLayer */
        $dataLayer = $apie
            ->getPlugin(CorePlugin::class)
            ->getApiResourceFactory()
            ->getApiResourceRetrieverInstance(MemoryDataLayer::class);
        $actual = $dataLayer->retrieve(SimplePopo::class, 'QPBQZRDZRRZYQVKA', []);
        $this->assertInstanceOf(SimplePopo::class, $actual);
        $this->assertEquals('any', $actual->arbitraryField);

        /** @var MockApiResourceDataLayer $mockedDataLayer */
        $mockedDataLayer = $apie
            ->getPlugin(MockPlugin::class)
            ->getApiResourceFactory()
            ->getApiResourceRetrieverInstance(FileStorageDataLayer::class);
        $this->expectException(ResourceNotFoundException::class);
        $mockedDataLayer->retrieve(SimplePopo::class, 'QPBQZRDZRRZYQVKA', []);
    }
}
