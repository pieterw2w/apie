<?php


namespace W2w\Test\Apie\Features\SubActions;

use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Apie;
use W2w\Lib\Apie\DefaultApie;
use W2w\Lib\Apie\PluginInterfaces\SubActionsProviderInterface;
use W2w\Lib\Apie\Plugins\StaticConfig\StaticConfigPlugin;
use W2w\Lib\Apie\Plugins\StaticConfig\StaticResourcesPlugin;
use W2w\Test\Apie\Features\AnotherSimplePopo;
use W2w\Test\Apie\Mocks\ApiResources\FullRestObject;
use W2w\Test\Apie\Mocks\ApiResources\SimplePopo;
use W2w\Test\Apie\Mocks\ApiResources\SumExample;
use W2w\Test\Apie\Mocks\SubActions\SupportedAwareSubAction;
use W2w\Test\Apie\Mocks\SubActions\WithAdditionalArguments;
use W2w\Test\Apie\Mocks\SubActions\WithoutTypehintInHandle;
use W2w\Test\Apie\Mocks\SubActions\WithTypehintInHandle;

class SubActionsFeatureTest extends TestCase
{
    /**
     * @var Apie
     */
    private $testItem;

    protected function setUp(): void
    {
        $subActionProviderPlugin = new class implements SubActionsProviderInterface {
            public function getSubActions()
            {
                // anonymous classes are not possible!
                $action1 = new WithTypehintInHandle();
                $action2 = new SupportedAwareSubAction();
                $action3 = new WithoutTypehintInHandle();
                $action4 = new WithAdditionalArguments();
                return [
                    'md5' => [$action2, $action3],
                    'reflection' => [$action1, $action3],
                    'other' => [$action1, $action2, $action3],
                    'sub' => [$action4],
                ];
            }
        };
        $plugins = [
            $subActionProviderPlugin,
            new StaticConfigPlugin('http://sub-action.api.nl'),
            new StaticResourcesPlugin([FullRestObject::class, SimplePopo::class, SumExample::class, AnotherSimplePopo::class]),
        ];
        $this->testItem = DefaultApie::createDefaultApie(true, $plugins);
    }

    public function testSubActionIsMadeInSchema()
    {
        // file_put_contents(__DIR__ . '/expected-specs.yml', $this->testItem->getOpenApiSpecGenerator()->getOpenApiSpec()->toYaml(20, 2));

        $this->assertEquals(
            file_get_contents(__DIR__ . '/expected-specs.yml'),
            $this->testItem->getOpenApiSpecGenerator()->getOpenApiSpec()->toYaml(20, 2)
        );
    }

    public function testFacadeCallWorks()
    {
        $facade = $this->testItem->getApiResourceFacade();
        $request = new ServerRequest('POST', '/another_simple_popo', ['Content-Type' => 'application/json'], '{}');
        /** @var AnotherSimplePopo $resource */
        $resource = $facade->post(AnotherSimplePopo::class, $request)->getResource();

        $request = new ServerRequest('POST', '/another_simple_popo/12/sub/', ['Content-Type' => 'application/json'], '{"one":5,"two":5}');

        $response = $this->testItem->getApiResourceFacade()->postSubAction(AnotherSimplePopo::class, $resource->getId(), 'sub', $request);
        $this->assertEquals(new SumExample($resource->getId() + 5, 5), $response->getResource());
    }
}
