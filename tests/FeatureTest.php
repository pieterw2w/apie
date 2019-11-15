<?php
namespace W2w\Test\Apie;

use DateTimeInterface;
use erasys\OpenApi\Spec\v3\Info;
use erasys\OpenApi\Spec\v3\Schema;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Ramsey\Uuid\Uuid;
use W2w\Lib\Apie\ApiResources\App;
use W2w\Lib\Apie\Retrievers\AppRetriever;
use W2w\Lib\Apie\Retrievers\ArrayPersister;
use W2w\Lib\Apie\ServiceLibraryFactory;
use W2w\Test\Apie\Mocks\Data\FullRestObject;
use W2w\Test\Apie\Mocks\Data\SimplePopo;
use W2w\Test\Apie\Mocks\Data\SumExample;
use W2w\Test\Apie\OpenApiSchema\Data\MultipleTypesObject;

class FeatureTest extends TestCase
{
    public function test_service_library_defaults_post_work()
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

    public function test_service_library_defaults_crud_works()
    {
        srand(0);
        $expected = new FullRestObject(Uuid::fromString('986e12c4-3011-4ed8-aead-c62b76bb7f69'));
        srand(0);
        $testItem = new ServiceLibraryFactory([FullRestObject::class], true, null);
        // we need to always have the same instance of ArrayPersister.
        $container = new class implements ContainerInterface
        {
            private $persister;

            public function get($id)
            {
                if (!$this->persister) {
                    $this->persister = new ArrayPersister();
                }
                return $this->persister;
            }

            public function has($id)
            {
                return $id === ArrayPersister::class;
            }
        };
        $testItem->setContainer($container);

        // first create resource
        $request = new Request('POST', '/full_rest_object/', [], '{"uuid":"986e12c4-3011-4ed8-aead-c62b76bb7f69"}');
        $this->assertEquals(
            $expected->getUuid(),
            $testItem->getApiResourceFacade()->post(FullRestObject::class, $request)->getResource()->getUuid()
        );
        // now put the resource
        $request = new Request('PUT', '/full_rest_object/986e12c4-3011-4ed8-aead-c62b76bb7f69', [], '{"string_value":"strings"}');
        $actual = $testItem->getApiResourceFacade()->put(FullRestObject::class, '986e12c4-3011-4ed8-aead-c62b76bb7f69', $request);
        $expected->stringValue = "strings";
        $this->assertEquals(
            $expected,
            $actual->getResource()
        );

        $request = new Request('GET', '/full_rest_object/', []);
        $this->assertEquals(
            [$expected],
            $testItem->getApiResourceFacade()->getAll(FullRestObject::class, 0, 10, $request)->getResource()
        );

        $this->assertEquals(
            null,
            $testItem->getApiResourceFacade()->delete(FullRestObject::class, '986e12c4-3011-4ed8-aead-c62b76bb7f69')->getResource()
        );
        $this->assertEquals(
            [],
            $testItem->getApiResourceFacade()->getAll(FullRestObject::class, 0, 10, $request)->getResource()
        );
    }

    public function test_serialized_name_works_as_intended()
    {
        $testItem = new ServiceLibraryFactory([MultipleTypesObject::class], true, null);
        $request = new Request('POST', '/sum_example/', [], '{"name":"test"}');
        $actual = $testItem->getApiResourceFacade()->post(MultipleTypesObject::class, $request);
        $expected = new MultipleTypesObject();
        $expected->myMetadataIsADifferentName = "test";
        $this->assertEquals(
            $expected,
            $actual->getResource()
        );
        $this->assertEquals(
            json_encode([
                "floating_point" => null,
                "double" => null,
                "integer" => null,
                "boolean" => null,
                "array" => null,
                "string_array" => null,
                "object_array" => null,
                "value_object" => null,
                "name" => "test"
            ]),
            (string) $actual->getResponse()->getBody()
        );
    }

    public function test_service_github_issue_1()
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
    }

    public function test_service_library_create_open_api_schema()
    {
        $testItem = new ServiceLibraryFactory([App::class, SimplePopo::class, FullRestObject::class], true, null);
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
        $testItem->getSchemaGenerator()->defineSchemaForResource(DateTimeInterface::class, new Schema(['type' => 'string', 'format' => 'date-time']));
        $testItem->getSchemaGenerator()->defineSchemaForResource(Uuid::class, new Schema(['format' => 'uuid', 'type' => 'string']));
        $testItem->setInfo(new Info('Unit test title', '1.0'));
        //file_put_contents(__DIR__ . '/expected-specs.json', json_encode($testItem->getOpenApiSpecGenerator('/test-url')->getOpenApiSpec()->toArray(), JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));

        $expected = json_decode(file_get_contents(__DIR__ . '/expected-specs.json'), true);
        $this->assertEquals(
            $expected,
            $testItem->getOpenApiSpecGenerator('/test-url')->getOpenApiSpec()->toArray()
        );
    }
}
