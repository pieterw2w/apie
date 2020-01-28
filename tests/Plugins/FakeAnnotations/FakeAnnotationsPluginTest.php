<?php


namespace W2w\Test\Apie\Plugins\FakeAnnotations;

use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Annotations\ApiResource;
use W2w\Lib\Apie\Apie;
use W2w\Lib\Apie\Plugins\Core\DataLayers\NullDataLayer;
use W2w\Lib\Apie\Plugins\FakeAnnotations\FakeAnnotationsPlugin;
use W2w\Lib\Apie\Plugins\StaticConfig\StaticConfigPlugin;
use W2w\Lib\Apie\Plugins\StatusCheck\ApiResources\Status;
use W2w\Lib\Apie\Plugins\StatusCheck\StatusCheckPlugin;

class FakeAnnotationsPluginTest extends TestCase
{
    /**
     * @var Apie
     */
    private $apie;

    protected function setUp(): void
    {
        $config = [
            Status::class => ApiResource::createFromArray([
                'retrieveClass' => NullDataLayer::class,
                'persistClass' => NullDataLayer::class
            ])
        ];
        $this->apie = new Apie([new StaticConfigPlugin(''), new FakeAnnotationsPlugin($config), new StatusCheckPlugin([])], true, null);
    }

    public function test_override_config_working()
    {
        $specGenerator = $this->apie->getOpenApiSpecGenerator();

        $doc = $specGenerator->getOpenApiSpec();

        $actual = $doc->paths['/status/{id}'];
        $this->assertNotNull($actual->get);
        $this->assertNotNull($actual->put);
        $this->assertNotNull($actual->delete);

        $actual = $doc->paths['/status'];
        $this->assertNotNull($actual->get);
        $this->assertNotNull($actual->post);
    }
}
