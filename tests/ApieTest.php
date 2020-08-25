<?php


namespace W2w\Test\Apie;

use Doctrine\Common\Annotations\AnnotationReader;
use erasys\OpenApi\Spec\v3\Info;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use W2w\Lib\Apie\Apie;
use W2w\Lib\Apie\Core\ApiResourceFacade;
use W2w\Lib\Apie\Exceptions\BadConfigurationException;
use W2w\Lib\Apie\OpenApiSchema\OpenApiSpecGenerator;
use W2w\Lib\Apie\Plugins\Core\Serializers\SymfonySerializerAdapter;
use W2w\Lib\Apie\Plugins\StaticConfig\StaticConfigPlugin;
use W2w\Lib\ApieObjectAccessNormalizer\ObjectAccess\GroupedObjectAccess;

class ApieTest extends TestCase
{
    public function test_invalid_plugin_throws_exception()
    {
        $this->expectException(BadConfigurationException::class);
        new Apie([$this], true, null);
    }

    /**
     * @dataProvider noPluginsThrowExceptionsProvider
     */
    public function test_no_plugins_throw_exceptions(string $expectedException, string $method)
    {
        $testItem = new Apie([], true, null, false);
        $this->assertEquals(true, $testItem->isDebug());
        $this->expectException($expectedException);
        $testItem->$method();
    }

    public function noPluginsThrowExceptionsProvider()
    {
        yield [BadConfigurationException::class, 'getResourceSerializer'];
        yield [BadConfigurationException::class, 'getClassMetadataFactory'];
        yield [BadConfigurationException::class, 'getPropertyConverter'];
        yield [BadConfigurationException::class, 'getCacheItemPool'];
        yield [BadConfigurationException::class, 'getAnnotationReader'];
        yield [BadConfigurationException::class, 'getResourceSerializer'];
        yield [BadConfigurationException::class, 'getApiResourceFacade'];
        yield [BadConfigurationException::class, 'getOpenApiSpecGenerator'];
        yield [BadConfigurationException::class, 'getBaseUrl'];
    }

    /**
     * @dataProvider corePluginNoExceptionProvider
     */
    public function test_core_plugin_No_exception(string $expectedClass, string $method)
    {
        $testItem = new Apie([new StaticConfigPlugin('')], true, null);
        $this->assertEquals(true, $testItem->isDebug());
        $this->assertEquals($expectedClass, get_class($testItem->$method()));
    }

    public function corePluginNoExceptionProvider()
    {
        yield [SymfonySerializerAdapter::class, 'getResourceSerializer'];
        yield [ClassMetadataFactory::class, 'getClassMetadataFactory'];
        yield [MetadataAwareNameConverter::class, 'getPropertyConverter'];
        yield [GroupedObjectAccess::class, 'getObjectAccess'];
        yield [ArrayAdapter::class, 'getCacheItemPool'];
        yield [AnnotationReader::class, 'getAnnotationReader'];
        yield [SymfonySerializerAdapter::class, 'getResourceSerializer'];
        yield [ApiResourceFacade::class, 'getApiResourceFacade'];
        yield [Info::class, 'createInfo'];
        yield [OpenApiSpecGenerator::class, 'getOpenApiSpecGenerator'];
    }
}
