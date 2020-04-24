<?php

namespace W2w\Lib\Apie;

use Doctrine\Common\Annotations\Reader;
use erasys\OpenApi\Spec\v3\Document;
use erasys\OpenApi\Spec\v3\Info;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use W2w\Lib\Apie\Core\ApieCore;
use W2w\Lib\Apie\Core\ApiResourceFacade;
use W2w\Lib\Apie\Core\ClassResourceConverter;
use W2w\Lib\Apie\Core\Encodings\ChainableFormatRetriever;
use W2w\Lib\Apie\Core\IdentifierExtractor;
use W2w\Lib\Apie\Core\PluginContainer;
use W2w\Lib\Apie\Core\ResourceFactories\ChainableFactory;
use W2w\Lib\Apie\Exceptions\BadConfigurationException;
use W2w\Lib\Apie\Exceptions\NotAnApiePluginException;
use W2w\Lib\Apie\Interfaces\ApiResourceFactoryInterface;
use W2w\Lib\Apie\Interfaces\FormatRetrieverInterface;
use W2w\Lib\Apie\Interfaces\ResourceSerializerInterface;
use W2w\Lib\Apie\OpenApiSchema\OpenApiSpecGenerator;
use W2w\Lib\Apie\OpenApiSchema\SchemaGenerator;
use W2w\Lib\Apie\PluginInterfaces\AnnotationReaderProviderInterface;
use W2w\Lib\Apie\PluginInterfaces\ApieAwareInterface;
use W2w\Lib\Apie\PluginInterfaces\ApieConfigInterface;
use W2w\Lib\Apie\PluginInterfaces\ApiResourceFactoryProviderInterface;
use W2w\Lib\Apie\PluginInterfaces\CacheItemPoolProviderInterface;
use W2w\Lib\Apie\PluginInterfaces\EncoderProviderInterface;
use W2w\Lib\Apie\PluginInterfaces\NormalizerProviderInterface;
use W2w\Lib\Apie\PluginInterfaces\ObjectAccessProviderInterface;
use W2w\Lib\Apie\PluginInterfaces\OpenApiEventProviderInterface;
use W2w\Lib\Apie\PluginInterfaces\OpenApiInfoProviderInterface;
use W2w\Lib\Apie\PluginInterfaces\PropertyInfoExtractorProviderInterface;
use W2w\Lib\Apie\PluginInterfaces\ResourceProviderInterface;
use W2w\Lib\Apie\PluginInterfaces\SchemaProviderInterface;
use W2w\Lib\Apie\PluginInterfaces\SerializerProviderInterface;
use W2w\Lib\Apie\PluginInterfaces\SymfonyComponentProviderInterface;
use W2w\Lib\Apie\Plugins\Core\CorePlugin;
use W2w\Lib\ApieObjectAccessNormalizer\ObjectAccess\CachedObjectAccess;
use W2w\Lib\ApieObjectAccessNormalizer\ObjectAccess\GroupedObjectAccess;
use W2w\Lib\ApieObjectAccessNormalizer\ObjectAccess\ObjectAccess;
use W2w\Lib\ApieObjectAccessNormalizer\ObjectAccess\ObjectAccessInterface;

final class Apie implements SerializerProviderInterface,
    ResourceProviderInterface,
    NormalizerProviderInterface,
    EncoderProviderInterface,
    SymfonyComponentProviderInterface,
    CacheItemPoolProviderInterface,
    AnnotationReaderProviderInterface,
    ApiResourceFactoryProviderInterface,
    OpenApiInfoProviderInterface,
    ApieConfigInterface,
    SchemaProviderInterface,
    OpenApiEventProviderInterface,
    PropertyInfoExtractorProviderInterface
{
    const VERSION = "3.0";

    /**
     * @var bool
     */
    private $debug;

    /**
     * @var string|null
     */
    private $cacheFolder;

    /**
     * @var PluginContainer
     */
    private $pluginContainer;

    /**
     * @var ApieCore
     */
    private $apieCore;

    /**
     * @var ChainableFactory|null
     */
    private $chainableFactory;

    /**
     * @var ChainableFormatRetriever|null
     */
    private $chainableFormatRetriever;

    /**

     * @param object[] $plugins
     * @param bool $debug
     * @param string|null $cacheFolder
     * @param bool $addCorePlugin
     */
    public function __construct(array $plugins, bool $debug = false, ?string $cacheFolder = null, bool $addCorePlugin = true)
    {
        $this->debug = $debug;
        $this->cacheFolder = $cacheFolder;
        if ($addCorePlugin) {
            $plugins[] = new CorePlugin();
        }
        $this->pluginContainer = new PluginContainer($plugins);
        $this->pluginContainer->each(
            ApieAwareInterface::class,
            function (ApieAwareInterface $plugin) {
                $plugin->setApie($this);
            }
        );
        $this->apieCore = new ApieCore($this, $this->pluginContainer);
    }

    /**
     * Creates a new instance of Apie reusing the services/instances of the current Apie instance.
     *
     * @param array $plugins
     * @return Apie
     */
    public function createContext(array $plugins = []): self
    {
        $plugins[] = $this;
        return new Apie($plugins, $this->debug, $this->cacheFolder, false);
    }

    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * @return string|null
     */
    public function getCacheFolder(): ?string
    {
        return $this->cacheFolder;
    }

    public function getPlugin(string $pluginClass): object
    {
        return $this->pluginContainer->getPlugin($pluginClass);
    }

    /**
     * @return ResourceSerializerInterface
     */
    public function getResourceSerializer(): ResourceSerializerInterface
    {
        return $this->pluginContainer->first(
            SerializerProviderInterface::class,
            new BadConfigurationException('I have no resource serializer set up')
        )->getResourceSerializer();
    }

    /**
     * Returns a list of Api resources.
     *
     * @return string[]
     */
    public function getResources(): array
    {
        return array_values(array_unique($this->pluginContainer->merge(ResourceProviderInterface::class, 'getResources')));
    }

    /**
     * @return NormalizerInterface[]|DenormalizerInterface[]
     */
    public function getNormalizers(): array
    {
        return $this->pluginContainer->merge(NormalizerProviderInterface::class, 'getNormalizers');
    }

    /**
     * @return EncoderInterface[]|DecoderInterface[]
     */
    public function getEncoders(): array
    {
        return $this->pluginContainer->merge(EncoderProviderInterface::class, 'getEncoders');
    }

    public function getClassMetadataFactory(): ClassMetadataFactoryInterface
    {
        return $this->pluginContainer->first(
            SymfonyComponentProviderInterface::class,
            new BadConfigurationException('I have no symfony component provider set up')
        )->getClassMetadataFactory();
    }

    public function getPropertyConverter(): NameConverterInterface
    {
        return $this->pluginContainer->first(
            SymfonyComponentProviderInterface::class,
            new BadConfigurationException('I have no symfony component provider set up')
        )->getPropertyConverter();
    }

    public function getPropertyAccessor(): PropertyAccessor
    {
        return $this->pluginContainer->first(
            SymfonyComponentProviderInterface::class,
            new BadConfigurationException('I have no symfony component provider set up')
        )->getPropertyAccessor();
    }

    public function getPropertyTypeExtractor(): PropertyTypeExtractorInterface
    {
        return $this->pluginContainer->first(
            SymfonyComponentProviderInterface::class,
            new BadConfigurationException('I have no symfony component provider set up')
        )->getPropertyTypeExtractor();
    }

    public function getCacheItemPool(): CacheItemPoolInterface
    {
        return $this->pluginContainer->first(
            CacheItemPoolProviderInterface::class,
            new BadConfigurationException('I have no cache item pool provider set up')
        )->getCacheItemPool();
    }

    public function getAnnotationReader(): Reader
    {
        return $this->pluginContainer->first(
            AnnotationReaderProviderInterface::class,
            new BadConfigurationException('I have no annotation reader set up')
        )->getAnnotationReader();
    }

    public function getFormatRetriever(): FormatRetrieverInterface
    {
        if (!$this->chainableFormatRetriever) {
            $this->chainableFormatRetriever = new ChainableFormatRetriever(
                iterator_to_array($this->pluginContainer->combine(EncoderProviderInterface::class, 'getFormatRetriever'))
            );
        }
        return $this->chainableFormatRetriever;
    }

    public function getIdentifierExtractor(): IdentifierExtractor
    {
        return $this->apieCore->getIdentifierExtractor();
    }

    public function getApiResourceFacade(): ApiResourceFacade
    {
        return $this->apieCore->getApiResourceFacade();
    }

    public function getOpenApiSpecGenerator(): OpenApiSpecGenerator
    {
        return $this->apieCore->getOpenApiSpecGenerator();
    }

    public function getSchemaGenerator(): SchemaGenerator
    {
        return $this->apieCore->getSchemaGenerator();
    }

    public function getApiResourceFactory(): ApiResourceFactoryInterface
    {
        if (!$this->chainableFactory) {
            $this->chainableFactory = new ChainableFactory(
                iterator_to_array($this->pluginContainer->combine(ApiResourceFactoryProviderInterface::class, 'getApiResourceFactory'))
            );
        }
        return $this->chainableFactory;
    }

    public function createInfo(): Info
    {
        $res = $this->pluginContainer->first(OpenApiInfoProviderInterface::class, null);

        if (empty($res)) {
            return new Info('Apie', Apie::VERSION);
        }
        return $res->createInfo();
    }

    public function getBaseUrl(): string
    {
        return $this->pluginContainer->first(ApieConfigInterface::class, new BadConfigurationException('I have no config set up'))->getBaseUrl();
    }

    public function getDefinedStaticData(): array
    {
        $result = [];
        foreach (array_reverse($this->pluginContainer->getPluginsWithInterface(SchemaProviderInterface::class)) as $schemaDefinition) {
            foreach ($schemaDefinition->getDefinedStaticData() as $className => $schema) {
                $result[$className] = $schema;
            }
        }
        return $result;
    }

    public function getDynamicSchemaLogic(): array
    {
        $result = [];
        foreach (array_reverse($this->pluginContainer->getPluginsWithInterface(SchemaProviderInterface::class)) as $schemaDefinition) {
            foreach ($schemaDefinition->getDynamicSchemaLogic() as $className => $callable) {
                $result[$className] = $callable;
            }
        }
        return $result;
    }

    public function getClassResourceConverter(): ClassResourceConverter
    {
        return $this->apieCore->getClassResourceConverter();
    }

    public function onOpenApiDocGenerated(Document $document): Document
    {
        $this->pluginContainer->each(OpenApiEventProviderInterface::class, function (OpenApiEventProviderInterface $plugin) use (&$document) {
            $document = $plugin->onOpenApiDocGenerated($document);
        });
        return $document;
    }

    /**
     * @deprecated  use getObjectAccess instead
     */
    public function getListExtractors(): array
    {
        return $this->pluginContainer->merge(PropertyInfoExtractorProviderInterface::class, 'getListExtractors');
    }

    /**
     * @deprecated  use getObjectAccess instead
     */
    public function getTypeExtractors(): array
    {
        return $this->pluginContainer->merge(PropertyInfoExtractorProviderInterface::class, 'getTypeExtractors');
    }

    /**
     * @deprecated  use getObjectAccess instead
     */
    public function getDescriptionExtractors(): array
    {
        return $this->pluginContainer->merge(PropertyInfoExtractorProviderInterface::class, 'getDescriptionExtractors');
    }

    /**
     * @deprecated  use getObjectAccess instead
     */
    public function getAccessExtractors(): array
    {
        return $this->pluginContainer->merge(PropertyInfoExtractorProviderInterface::class, 'getAccessExtractors');
    }

    /**
     * @deprecated  use getObjectAccess instead
     */
    public function getInitializableExtractors(): array
    {
        return $this->pluginContainer->merge(PropertyInfoExtractorProviderInterface::class, 'getInitializableExtractors');
    }

    public function getObjectAccess(): ObjectAccessInterface
    {
        $objectAccess = new ObjectAccess();
        $objectAccesses = $this->pluginContainer->getPluginsWithInterface(ObjectAccessProviderInterface::class);
        if (!empty($objectAccesses)) {
            $list = [];
            foreach ($objectAccesses as $objectAccessPlugin) {
                $list = array_merge($list, $objectAccessPlugin->getObjectAccesses());
            }
            $objectAccess = new GroupedObjectAccess($objectAccess, $list);
        }
        if (!$this->debug && $this->cacheFolder) {
            return new CachedObjectAccess($objectAccess, $this->getCacheItemPool());
        }
        return $objectAccess;
    }
}
