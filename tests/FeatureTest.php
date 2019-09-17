<?php
namespace W2w\Test\Apie;

use erasys\OpenApi\Spec\v3\Info;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use W2w\Lib\Apie\ApiResources\App;
use W2w\Lib\Apie\Retrievers\AppRetriever;
use W2w\Lib\Apie\ServiceLibraryFactory;
use W2w\Test\Apie\Mocks\Data\SimplePopo;
use W2w\Test\Apie\Mocks\Data\SumExample;

class FeatureTest extends TestCase
{
    public function test_service_library_defaults_work()
    {
        srand(0);
        $expected = new SimplePopo();
        srand(0);
        $testItem = new ServiceLibraryFactory([SimplePopo::class], true, null);
        $request = new Request('POST', '/simple_popo/', [], '{}');
        $this->assertEquals(
            $expected->getId(),
            $testItem->getApiResourceFacade()->post(SimplePopo::class, $request)->getResource()->getId()
        );
    }

    /**public function test_service_github_issue_1()
    {
        $testItem = new ServiceLibraryFactory([SumExample::class], true, null);
        $request = new Request('POST', '/sum_example/', [], '{"one":1,"two":2}');
        $actual = $testItem->getApiResourceFacade()->post(SumExample::class, $request);
        $this->assertEquals(
            new SumExample(1, 2),
            $actual->getResource()
        );
        $this->assertEquals(
            '{"addition":3}',
            (string) $actual->getResponse()->getBody()
        );
    }**/

    public function test_service_library_create_open_api_schema()
    {
        $testItem = new ServiceLibraryFactory([App::class, SimplePopo::class], true, null);
        $container = new class implements ContainerInterface
        {
            public function get($id)
            {
                return new AppRetriever('unit test', 'development', 'haas525', true);
            }

            public function has($id)
            {
                return $id === AppRetriever::class;
            }
        };
        $testItem->setContainer($container);
        $testItem->setInfo(new Info('Unit test title', '1.0'));
        //file_put_contents(__DIR__ . '/expected-specs.json', json_encode($testItem->getOpenApiSpecGenerator('/test-url')->getOpenApiSpec()->toArray(), JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));

        $expected = json_decode(file_get_contents(__DIR__ . '/expected-specs.json'), true);
        $this->assertEquals(
            $expected,
            $testItem->getOpenApiSpecGenerator('/test-url')->getOpenApiSpec()->toArray()
        );
    }
}
