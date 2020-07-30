<?php

namespace W2w\Lib\Apie\Plugins\Core;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\PhpFileCache;
use Pjordaan\AlternateReflectionExtractor\ReflectionExtractor;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\SerializerExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Mapping\Loader\LoaderChain;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Serializer\Serializer;
use W2w\Lib\Apie\Interfaces\ApiResourceFactoryInterface;
use W2w\Lib\Apie\Interfaces\FormatRetrieverInterface;
use W2w\Lib\Apie\Interfaces\ResourceSerializerInterface;
use W2w\Lib\Apie\PluginInterfaces\AnnotationReaderProviderInterface;
use W2w\Lib\Apie\PluginInterfaces\ApieAwareInterface;
use W2w\Lib\Apie\PluginInterfaces\ApieAwareTrait;
use W2w\Lib\Apie\PluginInterfaces\ApiResourceFactoryProviderInterface;
use W2w\Lib\Apie\PluginInterfaces\CacheItemPoolProviderInterface;
use W2w\Lib\Apie\PluginInterfaces\EncoderProviderInterface;
use W2w\Lib\Apie\PluginInterfaces\NormalizerProviderInterface;
use W2w\Lib\Apie\PluginInterfaces\ObjectAccessProviderInterface;
use W2w\Lib\Apie\PluginInterfaces\ResourceLifeCycleInterface;
use W2w\Lib\Apie\PluginInterfaces\SerializerProviderInterface;
use W2w\Lib\Apie\PluginInterfaces\SymfonyComponentProviderInterface;
use W2w\Lib\Apie\Plugins\Core\Encodings\FormatRetriever;
use W2w\Lib\Apie\Plugins\Core\ResourceFactories\FallbackFactory;
use W2w\Lib\Apie\Plugins\Core\Serializers\Mapping\BaseGroupLoader;
use W2w\Lib\Apie\Plugins\Core\Normalizers\ExceptionNormalizer;
use W2w\Lib\Apie\Plugins\Core\Serializers\SymfonySerializerAdapter;
use W2w\Lib\ApieObjectAccessNormalizer\Normalizers\ApieObjectAccessNormalizer;
use W2w\Lib\ApieObjectAccessNormalizer\Normalizers\MethodCallDenormalizer;
use W2w\Lib\ApieObjectAccessNormalizer\ObjectAccess\ObjectAccessInterface;
use W2w\Lib\ApieObjectAccessNormalizer\ObjectAccess\SelfObjectAccess;
use W2w\Lib\ApieObjectAccessNormalizer\ObjectAccess\SelfObjectAccessInterface;

/**
 * Plugin with most default functionality.
 */
class CorePlugin implements SerializerProviderInterface,
    ApieAwareInterface,
    NormalizerProviderInterface,
    EncoderProviderInterface,
    SymfonyComponentProviderInterface,
    CacheItemPoolProviderInterface,
    AnnotationReaderProviderInterface,
    ApiResourceFactoryProviderInterface,
    ObjectAccessProviderInterface
{
    use ApieAwareTrait;

    private $classMetadataFactory;

    private $propertyConverter;

    private $propertyAccessor;

    private $propertyTypeExtractor;

    private $annotationReader;

    /**
     * {@inheritDoc}
     */
    public function getResourceSerializer(): ResourceSerializerInterface
    {
        $normalizers = $this->getApie()->getNormalizers();
        $encoders = $this->getApie()->getEncoders();
        $serializer = new Serializer($normalizers, $encoders);
        $lifecycles = $this->apie->getPluginsWithInterface(ResourceLifeCycleInterface::class);
        return new SymfonySerializerAdapter($serializer, $this->getApie()->getFormatRetriever(), $lifecycles);
    }

    /**
     * {@inheritDoc}
     */
    public function getNormalizers(): array
    {
        $apieObjectAccessNormalizer = new ApieObjectAccessNormalizer(
            $this->getApie()->getObjectAccess(),
            $this->getApie()->getPropertyConverter(),
            $this->getApie()->getClassMetadataFactory()
        );

        return [
            new ExceptionNormalizer($this->getApie()->isDebug()),
            new JsonSerializableNormalizer(),
            new ArrayDenormalizer(),
            new MethodCallDenormalizer($this->getApie()->getObjectAccess(), $apieObjectAccessNormalizer, $this->getApie()->getPropertyConverter()),
            $apieObjectAccessNormalizer,
        ];

    }

    /**
     * {@inheritDoc}
     */
    public function getClassMetadataFactory(): ClassMetadataFactoryInterface
    {
        if (!$this->classMetadataFactory) {
            $this->classMetadataFactory = new ClassMetadataFactory(
                new LoaderChain([
                    new AnnotationLoader($this->getApie()->getAnnotationReader()),
                    new BaseGroupLoader(['read', 'write', 'get', 'post', 'put']),
                ])
            );
        }
        return $this->classMetadataFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function getPropertyConverter(): NameConverterInterface
    {
        if (!$this->propertyConverter) {
            $classMetadataFactory = $this->getApie()->getClassMetadataFactory();
            $this->propertyConverter = new MetadataAwareNameConverter(
                $classMetadataFactory,
                new CamelCaseToSnakeCaseNameConverter()
            );
        }
        return $this->propertyConverter;
    }

    /**
     * {@inheritDoc}
     */
    public function getPropertyAccessor(): PropertyAccessor
    {
        if (!$this->propertyAccessor) {
            $this->propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
                ->setCacheItemPool($this->getApie()->getCacheItemPool())
                ->getPropertyAccessor();
        }
        return $this->propertyAccessor;
    }

    /**
     * {@inheritDoc}
     */
    public function getPropertyTypeExtractor(): PropertyTypeExtractorInterface
    {
        if (!$this->propertyTypeExtractor) {
            $factory = $this->getApie()->getClassMetadataFactory();
            $reflectionExtractor = new ReflectionExtractor();
            $phpDocExtractor = new PhpDocExtractor();

            $this->propertyTypeExtractor = new PropertyInfoExtractor(
                [
                    new SerializerExtractor($factory),
                    $reflectionExtractor,
                ] + $this->getApie()->getListExtractors(),
                $this->getApie()->getTypeExtractors() +[
                    $phpDocExtractor,
                    $reflectionExtractor,
                ],
                $this->getApie()->getDescriptionExtractors() + [
                    $phpDocExtractor,
                ] ,
                $this->getApie()->getAccessExtractors() + [
                    $reflectionExtractor,
                ] ,
                $this->getApie()->getInitializableExtractors() + [
                    $reflectionExtractor,
                ]
            );
        }
        return $this->propertyTypeExtractor;
    }

    /**
     * {@inheritDoc}
     */
    public function getCacheItemPool(): CacheItemPoolInterface
    {
        return new ArrayAdapter(0, true);
    }

    /**
     * {@inheritDoc}
     */
    public function getAnnotationReader(): Reader
    {
        if (!$this->annotationReader) {
            /** @scrutinizer ignore-deprecated */AnnotationRegistry::registerLoader('class_exists');
            if (class_exists(PhpFileCache::class) && $this->getApie()->getCacheFolder()) {
                $this->annotationReader = new CachedReader(
                    new AnnotationReader(),
                    new PhpFileCache($this->getApie()->getCacheFolder() . DIRECTORY_SEPARATOR . '/doctrine-cache'),
                    $this->getApie()->isDebug()
                );
            } else {
                $this->annotationReader = new AnnotationReader();
            }
        }
        return $this->annotationReader;
    }

    /**
     * {@inheritDoc}
     */
    public function getEncoders(): array
    {
        return [
            new XmlEncoder([XmlEncoder::ROOT_NODE_NAME => 'item']),
            new JsonEncoder(
                new JsonEncode([JsonEncode::OPTIONS => JSON_UNESCAPED_SLASHES]),
                new JsonDecode([JsonDecode::ASSOCIATIVE => false])
            )
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getFormatRetriever(): FormatRetrieverInterface
    {
        return new FormatRetriever([
            'application/json' => 'json',
            'application/xml' => 'xml',
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getApiResourceFactory(): ApiResourceFactoryInterface
    {
        return new FallbackFactory(
            $this->getApie()->getPropertyAccessor(),
            $this->getApie()->getIdentifierExtractor()
        );
    }

    /**
     * @return ObjectAccessInterface[]
     */
    public function getObjectAccesses(): array
    {
        return [
            SelfObjectAccessInterface::class => new SelfObjectAccess()
        ];
    }
}
