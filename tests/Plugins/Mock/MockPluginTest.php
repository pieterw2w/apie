<?php

namespace W2w\Test\Apie\Plugins\Mock;

use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Apie;
use W2w\Lib\Apie\Plugins\ApplicationInfo\ApplicationInfoPlugin;
use W2w\Lib\Apie\Plugins\ApplicationInfo\DataLayers\ApplicationInfoRetriever;
use W2w\Lib\Apie\Plugins\Mock\DataLayers\MockApiResourceDataLayer;
use W2w\Lib\Apie\Plugins\Mock\MockPlugin;

class MockPluginTest extends TestCase
{
    /**
     * @var Apie
     */
    private $apie;

    protected function setUp(): void
    {
        $this->apie = new Apie([new MockPlugin(), new ApplicationInfoPlugin()], true, null);
    }

    public function test_resource_retriever_is_overwritten()
    {
        $factory = $this->apie->getApiResourceFactory();
        $this->assertInstanceOf(
            MockApiResourceDataLayer::class,
            $factory->getApiResourceRetrieverInstance(ApplicationInfoRetriever::class)
        );
        $this->assertFalse($factory->hasApiResourcePersisterInstance(ApplicationInfoRetriever::class));
    }
}
