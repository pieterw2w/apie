<?php
namespace W2w\Test\Apie;

use DateTimeInterface;
use erasys\OpenApi\Spec\v3\Schema;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use W2w\Lib\Apie\Annotations\ApiResource;
use W2w\Lib\Apie\DefaultApie;
use W2w\Lib\Apie\Events\DecodeEvent;
use W2w\Lib\Apie\Events\DeleteResourceEvent;
use W2w\Lib\Apie\Events\ModifySingleResourceEvent;
use W2w\Lib\Apie\Events\NormalizeEvent;
use W2w\Lib\Apie\Events\ResponseEvent;
use W2w\Lib\Apie\Events\RetrievePaginatedResourcesEvent;
use W2w\Lib\Apie\Events\RetrieveSingleResourceEvent;
use W2w\Lib\Apie\Events\StoreExistingResourceEvent;
use W2w\Lib\Apie\Events\StoreNewResourceEvent;
use W2w\Lib\Apie\Exceptions\MethodNotAllowedException;
use W2w\Lib\Apie\OpenApiSchema\Factories\SchemaFactory;
use W2w\Lib\Apie\PluginInterfaces\ResourceLifeCycleInterface;
use W2w\Lib\Apie\Plugins\ApplicationInfo\ApiResources\ApplicationInfo;
use W2w\Lib\Apie\Plugins\FakeAnnotations\FakeAnnotationsPlugin;
use W2w\Lib\Apie\Plugins\StaticConfig\StaticConfigPlugin;
use W2w\Lib\Apie\Plugins\StaticConfig\StaticResourcesPlugin;
use W2w\Lib\Apie\Plugins\StatusCheck\ApiResources\Status;
use W2w\Lib\ApieObjectAccessNormalizer\Exceptions\ValidationException;
use W2w\Test\Apie\Mocks\ApiResources\FullRestObject;
use W2w\Test\Apie\Mocks\ApiResources\SimplePopo;
use W2w\Test\Apie\Mocks\ApiResources\SumExample;
use W2w\Test\Apie\Mocks\Plugins\MemoryDataLayerWithLargeDataPlugin;
use W2w\Test\Apie\OpenApiSchema\Data\MultipleTypesObject;
use W2w\Test\Apie\OpenApiSchema\Data\RecursiveObject;
use W2w\Test\Apie\OpenApiSchema\Data\RecursiveObjectWithId;

class FeatureTest extends TestCase implements ResourceLifeCycleInterface
{
    private $eventList = [];

    protected function setUp(): void
    {
        $this->eventList = [];
    }

    public function test_service_library_defaults_post_work()
    {
        srand(0);
        $expected = new SimplePopo();
        srand(0);
        $testItem = DefaultApie::createDefaultApie(true, [new StaticResourcesPlugin([SimplePopo::class]), $this]);
        $request = new ServerRequest('POST', '/simple_popo/', [], '{}');
        $this->assertEquals(
            $expected->getId(),
            $testItem->getApiResourceFacade()->post(SimplePopo::class, $request)->getResource()->getId()
        );
        $this->assertEventList(
            ['onPreCreateResource', SimplePopo::class],
            ['onPreDecodeRequestBody', SimplePopo::class],
            ['onPostDecodeRequestBody', SimplePopo::class],
            ['onPostCreateResource', SimplePopo::class],
            ['onPrePersistNewResource', SimplePopo::class],
            ['onPostPersistNewResource', SimplePopo::class]
        );
    }

    public function test_service_library_override_annotation_works()
    {
        $plugins = [
            new FakeAnnotationsPlugin([SimplePopo::class => new ApiResource()]),
            new StaticResourcesPlugin([SimplePopo::class]),
            $this,
        ];
        $testItem = DefaultApie::createDefaultApie(true, $plugins);
        $request = new ServerRequest('POST', '/simple_popo/', [], '{}');
        $this->expectException(MethodNotAllowedException::class);
        $testItem->getApiResourceFacade()->post(SimplePopo::class, $request)->getResource();
        $this->assertEquals([], $this->eventList);

    }

    public function test_service_library_defaults_crud_works()
    {
        srand(0);
        $expected = new FullRestObject(Uuid::fromString('986e12c4-3011-4ed8-aead-c62b76bb7f69'));
        srand(0);

        $plugins = [
            new StaticResourcesPlugin([FullRestObject::class]),
            $this
        ];

        $testItem = DefaultApie::createDefaultApie(true, $plugins);
        $facade = $testItem->getApiResourceFacade();
        // first create resource
        $request = new ServerRequest('POST', '/full_rest_object/', [], '{"uuid":"986e12c4-3011-4ed8-aead-c62b76bb7f69"}');
        $this->assertEquals(
            $expected->getUuid(),
            $facade->post(FullRestObject::class, $request)->getResource()->getUuid()
        );
        $request = new ServerRequest('GET', '/full_rest_object/', []);
        $this->assertEquals(
            [$expected],
            $facade->getAll(FullRestObject::class, $request)->getResource()
        );

        // now put the resource
        $request = new ServerRequest('PUT', '/full_rest_object/986e12c4-3011-4ed8-aead-c62b76bb7f69', [], '{"string_value":"strings"}');
        $actual = $facade->put(FullRestObject::class, '986e12c4-3011-4ed8-aead-c62b76bb7f69', $request);
        $expected->stringValue = "strings";
        $this->assertEquals(
            $expected,
            $actual->getResource()
        );

        $request = new ServerRequest('GET', '/full_rest_object/', []);
        $this->assertEquals(
            [$expected],
            $facade->getAll(FullRestObject::class, $request)->getResource()
        );

        $this->assertEquals(
            null,
            $facade->delete(FullRestObject::class, '986e12c4-3011-4ed8-aead-c62b76bb7f69')->getResource()
        );
        $this->assertEquals(
            [],
            $facade->getAll(FullRestObject::class, $request)->getResource()
        );
        $this->assertEventList(
            ['onPreCreateResource', FullRestObject::class],
            ['onPreDecodeRequestBody', FullRestObject::class],
            ['onPostDecodeRequestBody', FullRestObject::class],
            ['onPostCreateResource', FullRestObject::class],
            ['onPrePersistNewResource', FullRestObject::class],
            ['onPostPersistNewResource', FullRestObject::class],
            ['onPreRetrieveAllResources', FullRestObject::class],
            ['onPostRetrieveAllResources', FullRestObject::class],
            ['onPreRetrieveResource', FullRestObject::class],
            ['onPostRetrieveResource', FullRestObject::class],
            ['onPreModifyResource', FullRestObject::class],
            ['onPreDecodeRequestBody', SimplePopo::class],
            ['onPostDecodeRequestBody', SimplePopo::class],
            ['onPostModifyResource', FullRestObject::class],
            ['onPrePersistExistingResource', FullRestObject::class],
            ['onPostPersistExistingResource', FullRestObject::class],
            ['onPreRetrieveAllResources', FullRestObject::class],
            ['onPostRetrieveAllResources', FullRestObject::class],
            ['onPreDeleteResource', FullRestObject::class],
            ['onPostDeleteResource', FullRestObject::class],
            ['onPreRetrieveAllResources', FullRestObject::class],
            ['onPostRetrieveAllResources', FullRestObject::class]
        );
    }

    public function test_iterator_for_list_works_as_intended()
    {
        $testItem = DefaultApie::createDefaultApie(true);
        $request = new ServerRequest('GET', '/status/');
        $actual = $testItem->getApiResourceFacade()->getAll(Status::class, $request);
        $this->assertEquals(
            json_encode([
            ]),
            (string) $actual->getResponse()->getBody()
        );
    }

    public function test_serialized_name_works_as_intended()
    {
        $testItem = DefaultApie::createDefaultApie(true, [new StaticResourcesPlugin([MultipleTypesObject::class])]);
        $request = new ServerRequest('POST', '/sum_example/', [], '{"name":"test"}');
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

    public function test_search_filter_works_as_intended()
    {
        $plugins = [new MemoryDataLayerWithLargeDataPlugin(), new StaticResourcesPlugin([FullRestObject::class])];
        $testItem = DefaultApie::createDefaultApie(true, $plugins);

        $request = (new ServerRequest('GET', '/full_rest_object/'))
            ->withQueryParams(['stringValue' => 'value1', 'page' => 1, 'limit' => 500]);
        $actual = $testItem->getApiResourceFacade()->getAll(FullRestObject::class, $request);
        $this->assertEquals(
            [],
            $actual->getResource()
        );
        $request = (new ServerRequest('GET', '/full_rest_object/'))
            ->withQueryParams(['stringValue' => 'value1', 'valueObject' => 'pizza', 'page' => 0, 'limit' => 500]);
        $actual = $testItem->getApiResourceFacade()->getAll(FullRestObject::class, $request);
        $this->assertCount(
            50,
            $actual->getResource()
        );
        foreach ($actual->getResource() as $item) {
            $this->assertEquals('value1', $item->stringValue);
        }
    }

    public function test_service_github_issue_1()
    {
        $testItem = DefaultApie::createDefaultApie(true, [new StaticResourcesPlugin([SumExample::class])]);
        $request = new ServerRequest('POST', '/sum_example/', [], '{"one":1,"two":2}');
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
        $plugins = [
            new StaticResourcesPlugin([ApplicationInfo::class, SimplePopo::class, FullRestObject::class, RecursiveObject::class, RecursiveObjectWithId::class]),
            new StaticConfigPlugin('/test-url'),
        ];
        $testItem = DefaultApie::createDefaultApie(true, $plugins);
        $testItem->getSchemaGenerator()->defineSchemaForResource(DateTimeInterface::class, SchemaFactory::createStringSchema('date-time'));
        $testItem->getSchemaGenerator()->defineSchemaForResource(Uuid::class, SchemaFactory::createStringSchema('uuid'));
        // file_put_contents(__DIR__ . '/expected-specs.yml',$testItem->getOpenApiSpecGenerator('/test-url')->getOpenApiSpec()->toYaml(20, 2));

        $this->assertEquals(
            file_get_contents(__DIR__ . '/expected-specs.yml'),
            $testItem->getOpenApiSpecGenerator()->getOpenApiSpec()->toYaml(20, 2)
        );
    }

    /**
     * @dataProvider serializeErrorsToValidationExceptionProvider
     */
    public function test_serialize_errors_to_validation_exception(array $expectedErrors, array $expectedErrorsOld, string $outputClass, array $data)
    {
        // this tests requires a properly configured property type extractor, Apie provides a
        // proper one with help of CorePlugin, even though this makes it almost a feature test and not a unit test.
        $tmp = DefaultApie::createDefaultApie(true, []);
        $serializer = $tmp->getResourceSerializer();
        try {
            $serializer->postData($outputClass, json_encode($data), 'application/json');
            $this->fail('A validation exception should have been thrown!');
        } catch (ValidationException $validationException) {
            $this->assertEquals($expectedErrors, $validationException->getErrors());
        }
    }

    public function serializeErrorsToValidationExceptionProvider()
    {
        yield [
            ['one' => ['one is required'], 'two' => ['two is required']],
            ['one' => ['one is required']],
            SumExample::class,
            []
        ];
        yield [
            ['one' => ['must be one of "float" ("this is not a number" given)']],
            ['one' => ['must be one of "float" ("string" given)']],
            SumExample::class,
            ['one' => 'this is not a number', 'two' => 12]
        ];
    }

    public function onPreDeleteResource(DeleteResourceEvent $event)
    {
        $this->eventList[] = [__FUNCTION__, $event];
    }

    public function onPostDeleteResource(DeleteResourceEvent $event)
    {
        $this->eventList[] = [__FUNCTION__, $event];
    }

    public function onPreRetrieveResource(RetrieveSingleResourceEvent $event)
    {
        $this->eventList[] = [__FUNCTION__, $event];
    }

    public function onPostRetrieveResource(RetrieveSingleResourceEvent $event)
    {
        $this->eventList[] = [__FUNCTION__, $event];
    }

    public function onPreRetrieveAllResources(RetrievePaginatedResourcesEvent $event)
    {
        $this->eventList[] = [__FUNCTION__, $event];
    }

    public function onPostRetrieveAllResources(RetrievePaginatedResourcesEvent $event)
    {
        $this->eventList[] = [__FUNCTION__, $event];
    }

    public function onPrePersistExistingResource(StoreExistingResourceEvent $event)
    {
        $this->eventList[] = [__FUNCTION__, $event];
    }

    public function onPostPersistExistingResource(StoreExistingResourceEvent $event)
    {
        $this->eventList[] = [__FUNCTION__, $event];
    }

    public function onPreModifyResource(ModifySingleResourceEvent $event)
    {
        $this->eventList[] = [__FUNCTION__, $event];
    }

    public function onPostModifyResource(ModifySingleResourceEvent $event)
    {
        $this->eventList[] = [__FUNCTION__, $event];
    }

    public function onPreCreateResource(StoreNewResourceEvent $event)
    {
        $this->eventList[] = [__FUNCTION__, $event];
    }

    public function onPostCreateResource(StoreNewResourceEvent $event)
    {
        $this->eventList[] = [__FUNCTION__, $event];
    }

    public function onPrePersistNewResource(StoreExistingResourceEvent $event)
    {
        $this->eventList[] = [__FUNCTION__, $event];
    }

    public function onPostPersistNewResource(StoreExistingResourceEvent $event)
    {
        $this->eventList[] = [__FUNCTION__, $event];
    }

    public function onPreCreateResponse(ResponseEvent $event)
    {
        $this->eventList[] = [__FUNCTION__, $event];
    }

    public function onPostCreateResponse(ResponseEvent $event)
    {
        $this->eventList[] = [__FUNCTION__, $event];
    }

    public function onPreCreateNormalizedData(NormalizeEvent $event)
    {
        $this->eventList[] = [__FUNCTION__, $event];
    }

    public function onPostCreateNormalizedData(NormalizeEvent $event)
    {
        $this->eventList[] = [__FUNCTION__, $event];
    }

    public function onPreDecodeRequestBody(DecodeEvent $event)
    {
        $this->eventList[] = [__FUNCTION__, $event];
    }

    public function onPostDecodeRequestBody(DecodeEvent $event)
    {
        $this->eventList[] = [__FUNCTION__, $event];
    }

    public function assertEventList(...$expected)
    {
        foreach ($expected as $key => $expectedEvent) {
            $actualEvent = array_shift($this->eventList);
            $this->assertEquals($expectedEvent[0], $actualEvent[0], $key . 'th event is not the same');
            if (is_callable($actualEvent[1], 'getResourceClass')) {
                $this->assertEquals($expectedEvent[1], $actualEvent[1]->getResourceClass());
            } else if (is_callable($actualEvent[1], 'getResource')) {
                $this->assertEquals($expectedEvent[1], get_class($actualEvent[1]->getResource()));
            }
        }
    }
}
