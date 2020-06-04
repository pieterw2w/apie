<?php


namespace W2w\Test\Apie\Features\OpenApiSchema;

use DateTimeInterface;
use erasys\OpenApi\Spec\v3\Schema;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use ReflectionClass;
use W2w\Lib\Apie\DefaultApie;
use W2w\Lib\Apie\Plugins\ApplicationInfo\ApiResources\ApplicationInfo;
use W2w\Lib\Apie\Plugins\Core\Normalizers\ApieObjectNormalizer;
use W2w\Lib\Apie\Plugins\Core\Normalizers\ContextualNormalizer;
use W2w\Lib\Apie\Plugins\StaticConfig\StaticConfigPlugin;
use W2w\Lib\Apie\Plugins\StaticConfig\StaticResourcesPlugin;
use W2w\Test\Apie\Mocks\ApiResources\FullRestObject;
use W2w\Test\Apie\Mocks\ApiResources\SimplePopo;
use W2w\Test\Apie\OpenApiSchema\Data\RecursiveObject;
use W2w\Test\Apie\OpenApiSchema\Data\RecursiveObjectWithId;

class OpenApiSchemaGeneratorTest extends TestCase
{
    protected function setUp(): void
    {
        ContextualNormalizer::disableNormalizer(ApieObjectNormalizer::class);
    }

    protected function tearDown(): void
    {
        $this->hackCleanContextualNormalizer();
    }

    private function hackCleanContextualNormalizer()
    {
        $reflClass = new ReflectionClass(ContextualNormalizer::class);
        $prop = $reflClass->getProperty('globalDisabledNormalizers');
        $prop->setAccessible(true);
        $prop->setValue([]);

        $prop = $reflClass->getProperty('globalDisabledDenormalizers');
        $prop->setAccessible(true);
        $prop->setValue([]);
    }

    public function test_service_library_create_open_api_schema()
    {
        $plugins = [
            new StaticResourcesPlugin([ApplicationInfo::class, SimplePopo::class, FullRestObject::class, RecursiveObject::class, RecursiveObjectWithId::class]),
            new StaticConfigPlugin('/test-url'),
        ];
        $testItem = DefaultApie::createDefaultApie(true, $plugins);
        $testItem->getSchemaGenerator()->defineSchemaForResource(DateTimeInterface::class, new Schema(['type' => 'string', 'format' => 'date-time']));
        $testItem->getSchemaGenerator()->defineSchemaForResource(Uuid::class, new Schema(['format' => 'uuid', 'type' => 'string']));
        // file_put_contents(__DIR__ . '/expected-specs.yml', $testItem->getOpenApiSpecGenerator('/test-url')->getOpenApiSpec()->toYaml(20, 2));

        $this->assertEquals(
            file_get_contents(__DIR__ . '/expected-specs.yml'),
            $testItem->getOpenApiSpecGenerator()->getOpenApiSpec()->toYaml(20, 2)
        );
    }
}
