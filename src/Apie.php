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
     * @var SerializerProviderInterface[]
     */
    private $serializers = [];

    /**
     * @var ResourceProviderInterface[]
     */
    private $resources = [];

    /**
     * @var NormalizerProviderInterface[]
     */
    private $normalizers = [];

    /**
     * @var EncoderProviderInterface[]
     */
    private $encoders = [];

    /**
     * @var SymfonyComponentProviderInterface[]
     */
    private $symfonyComponents = [];

    /**
     * @var CacheItemPoolProviderInterface[]
     */
    private $cacheItemPools = [];

    /**
     * @var object[]
     */
    private $plugins = [];

    /**
     * @var AnnotationReaderProviderInterface[]
     */
    private $annotationReaders = [];

    /**
     * @var ApiResourceFactoryProviderInterface[]
     */
    private $apiResourceFactories = [];

    /**
     * @var OpenApiInfoProviderInterface[]
     */
    private $openApiInfoProviders = [];

    /**
     * @var ApieConfigInterface[]
     */
    private $configs = [];

    /**
     * @var SchemaProviderInterface[]
     */
    private $schemaDefinitions = [];

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
     * @var OpenApiEventProviderInterface[]
     */
    private $openApiEventProviders = [];

    /**
     * @var ObjectAccessProviderInterface[]
     */
    private $objectAccesses = [];

    /**
     * @var PropertyInfoExtractorProviderInterface[]
     */
    private $propertyInfoExtractors = [];

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
        static $mapping = [
            SerializerProviderInterface::class => 'serializers',
            ResourceProviderInterface::class => 'resources',
            NormalizerProviderInterface::class => 'normalizers',
            EncoderProviderInterface::class => 'encoders',
            SymfonyComponentProviderInterface::class => 'symfonyComponents',
            CacheItemPoolProviderInterface::class => 'cacheItemPools',
            AnnotationReaderProviderInterface::class => 'annotationReaders',
            ApiResourceFactoryProviderInterface::class => 'apiResourceFactories',
            OpenApiInfoProviderInterface::class => 'openApiInfoProviders',
            ApieConfigInterface::class => 'configs',
            SchemaProviderInterface::class => 'schemaDefinitions',
            OpenApiEventProviderInterface::class => 'openApiEventProviders',
            ObjectAccessProviderInterface::class => 'objectAccesses',
            PropertyInfoExtractorProviderInterface::class => 'propertyInfoExtractors',
        ];
        if ($addCorePlugin) {
            $plugins[] = new CorePlugin();
        }
        $this->plugins = $plugins;
        foreach ($plugins as $plugin) {
            $isUsed = false;
            foreach ($mapping as $className => $propertyName) {
                if ($plugin instanceof $className) {
                    $this->$propertyName[] = $plugin;
                    if (!$isUsed && $plugin instanceof ApieAwareInterface) {
                        $plugin->setApie($this);
                    }
                    $isUsed = true;
                }
            }
            if (!$isUsed) {
                throw new NotAnApiePluginException($plugin);
            }
        }
        $this->apieCore = new ApieCore($this);
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
        $last = null;
        foreach ($this->plugins as $plugin) {
            if ($plugin instanceof $pluginClass) {
                return $plugin;
            }
            $last = $plugin;
        }
        if ($last instanceof self) {
            return $last->getPlugin($pluginClass);
        }
        throw new BadConfigurationException('Plugin ' . $pluginClass . ' not found!');
    }

    /**
     * @return ResourceSerializerInterface
     */
    public function getResourceSerializer(): ResourceSerializerInterface
    {
        if (empty($this->serializers)) {
            throw new BadConfigurationException('I have no resource serializer set up');
        }
        return reset($this->serializers)->getResourceSerializer();
    }

    /**
     * Returns a list of Api resources.
     *
     * @return string[]
     */
    public function getResources(): array
    {
        $resources = [];
        foreach ($this->resources as $resourceProvider) {
            $resources = array_merge($resources, $resourceProvider->getResources());
        }
        return array_values(array_unique($resources));
    }

    /**
     * @return NormalizerInterface[]|DenormalizerInterface[]
     */
    public function getNormalizers(): array
    {
        $normalizers = [];
        foreach ($this->normalizers as $normalizerProvider) {
            $normalizers = array_merge($normalizers, $normalizerProvider->getNormalizers());
        }
        return $normalizers;
    }

    /**
     * @return EncoderInterface[]|DecoderInterface[]
     */
    public function getEncoders(): array
    {
        $encoders = [];
        foreach ($this->encoders as $encoderProvider) {
            $encoders = array_merge($encoders, $encoderProvider->getEncoders());
        }
        return $encoders;
    }

    public function getClassMetadataFactory(): ClassMetadataFactoryInterface
    {
        if (empty($this->symfonyComponents)) {
            throw new BadConfigurationException('I have no symfony component provider set up');
        }
        return reset($this->symfonyComponents)->getClassMetadataFactory();
    }

    public function getPropertyConverter(): NameConverterInterface
    {
        if (empty($this->symfonyComponents)) {
            throw new BadConfigurationException('I have no symfony component provider set up');
        }
        return reset($this->symfonyComponents)->getPropertyConverter();
    }

    public function getPropertyAccessor(): PropertyAccessor
    {
        if (empty($this->symfonyComponents)) {
            throw new BadConfigurationException('I have no symfony component provider set up');
        }
        return reset($this->symfonyComponents)->getPropertyAccessor();
    }

    public function getPropertyTypeExtractor(): PropertyTypeExtractorInterface
    {
        if (empty($this->symfonyComponents)) {
            throw new BadConfigurationException('I have no symfony component provider set up');
        }
        return reset($this->symfonyComponents)->getPropertyTypeExtractor();
    }

    public function getCacheItemPool(): CacheItemPoolInterface
    {
        if (empty($this->cacheItemPools)) {
            throw new BadConfigurationException('I have no cache item pool provider set up');
        }
        return reset($this->cacheItemPools)->getCacheItemPool();
    }

    public function getAnnotationReader(): Reader
    {
        if (empty($this->annotationReaders)) {
            throw new BadConfigurationException('I have no annotation reader set up');
        }
        return reset($this->annotationReaders)->getAnnotationReader();
    }

    public function getFormatRetriever(): FormatRetrieverInterface
    {
        if (!$this->chainableFormatRetriever) {
            $this->chainableFormatRetriever = new ChainableFormatRetriever(
                array_map(
                    function (EncoderProviderInterface $encoderProvider) {
                        return $encoderProvider->getFormatRetriever();
                    },
                    $this->encoders
                )
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
                array_map(
                    function (ApiResourceFactoryProviderInterface $factoryProvider) {
                        return $factoryProvider->getApiResourceFactory();
                    },
                    $this->apiResourceFactories
                )
            );
        }
        return $this->chainableFactory;
    }

    public function createInfo(): Info
    {
        if (empty($this->openApiInfoProviders)) {
            return new Info('Apie', Apie::VERSION);
        }
        return reset($this->openApiInfoProviders)->createInfo();
    }

    public function getBaseUrl(): string
    {
        if (empty($this->configs)) {
            throw new BadConfigurationException('I have no config set up');
        }
        return reset($this->configs)->getBaseUrl();
    }

    public function getDefinedStaticData(): array
    {
        $result = [];
        foreach (array_reverse($this->schemaDefinitions) as $schemaDefinition) {
            foreach ($schemaDefinition->getDefinedStaticData() as $className => $schema) {
                $result[$className] = $schema;
            }
        }
        return $result;
    }

    public function getDynamicSchemaLogic(): array
    {
        $result = [];
        foreach (array_reverse($this->schemaDefinitions) as $schemaDefinition) {
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
        foreach ($this->openApiEventProviders as $openApiEventProvider) {
            $document = $openApiEventProvider->onOpenApiDocGenerated($document);
        }
        return $document;
    }

    public function getListExtractors(): array
    {
        $result = [];
        foreach ($this->propertyInfoExtractors as $extractor) {
            $result  = $result + $extractor->getListExtractors();
        }
        return $result;
    }

    public function getTypeExtractors(): array
    {
        $result = [];
        foreach ($this->propertyInfoExtractors as $extractor) {
            $result  = $result + $extractor->getTypeExtractors();
        }
        return $result;
    }

    public function getDescriptionExtractors(): array
    {
        $result = [];
        foreach ($this->propertyInfoExtractors as $extractor) {
            $result  = $result + $extractor->getDescriptionExtractors();
        }
        return $result;
    }

    public function getAccessExtractors(): array
    {
        $result = [];
        foreach ($this->propertyInfoExtractors as $extractor) {
            $result  = $result + $extractor->getAccessExtractors();
        }
        return $result;
    }

    public function getInitializableExtractors(): array
    {
        $result = [];
        foreach ($this->propertyInfoExtractors as $extractor) {
            $result  = $result + $extractor->getInitializableExtractors();
        }
        return $result;
    }

    public function getObjectAccess(): ObjectAccessInterface
    {
        $objectAccess = new ObjectAccess();
        if (!empty($this->objectAccesses)) {
            $list = [];
            foreach ($this->objectAccesses as $objectAccess) {
                $list = array_merge($list, $objectAccess->getObjectAccesses());
            }
            $objectAccess = new GroupedObjectAccess($objectAccess, $list);
        }
        if (!$this->debug && $this->cacheFolder) {
            return new CachedObjectAccess($objectAccess, $this->getCacheItemPool());
        }
        return $objectAccess;
    }
}
