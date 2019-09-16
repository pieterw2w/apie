<?php
namespace W2w\Lib\Apie;

use Carbon\CarbonInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\PhpFileCache;
use PhpValueObjects\AbstractStringValueObject;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\Extractor\SerializerExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorFromClassMetadata;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Mapping\Loader\LoaderChain;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use W2w\Lib\Apie\ApiResources\App;
use W2w\Lib\Apie\ApiResources\Status;
use W2w\Lib\Apie\Encodings\FormatRetriever;
use W2w\Lib\Apie\Normalizers\ContextualNormalizer;
use W2w\Lib\Apie\Normalizers\EvilReflectionPropertyNormalizer;
use W2w\Lib\Apie\Normalizers\ExceptionNormalizer;
use W2w\Lib\Apie\Normalizers\StringValueObjectNormalizer;

/**
 * To avoid lots of boilerplate in using the library, this class helps in making sensible defaults.
 */
class ServiceLibraryFactory
{
    /**
     * @var boolean
     */
    private $debug;

    /**
     * @var string[]
     */
    private $apiResourceClasses;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ApiResourceFacade
     */
    private $apiResourceFacade;

    /**
     * @var ApiResources
     */
    private $apiResources;

    /**
     * @var ApiResourceRetriever
     */
    private $apiResourceRetriever;

    /**
     * @var ApiResourcePersister
     */
    private $apiResourcePersister;

    /**
     * @var ApiResourceFactoryInterface
     */
    private $apiResourceFactory;

    /**
     * @var ApiResourceMetadataFactory
     */
    private $apiResourceMetadatafactory;

    /**
     * @var Reader
     */
    private $annotationReader;

    /**
     * @var ClassResourceConverter
     */
    private $classResourceConverter;

    /**
     * @var FormatRetriever
     */
    private $formatRetriever;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var NameConverterInterface
     */
    private $propertyConverter;

    /**
     * @var ClassMetadataFactoryInterface
     */
    private $classMetadataFactory;

    /**
     * @var string|null
     */
    private $cacheFolder;

    /**
     * @var (NormalizerInterface|DenormalizerInterface)[]
     */
    private $normalizers;

    /**
     * @var (NormalizerInterface|DenormalizerInterface)[]
     */
    private $additionalNormalizers;

    /**
     * @var EncoderInterface[]
     */
    private $encoders;

    /**
     * @var CacheItemPoolInterface
     */
    private $serializerCache;

    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;

    /**
     * @var PropertyTypeExtractorInterface
     */
    private $propertyTypeExtractor;

    /**
     * @param string[] $apiResourceClasses
     * @param bool $debug
     * @param string|null $cacheFolder
     */
    public function __construct(array $apiResourceClasses = [App::class, Status::class], bool $debug = false, ?string $cacheFolder = null)
    {
        $this->apiResourceClasses = $apiResourceClasses;
        $this->debug = $debug;
        $this->cacheFolder = $cacheFolder;
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }

    public function getCacheFolder(): ?string
    {
        return $this->cacheFolder;
    }

    public function setSerializer(SerializerInterface $serializer): self
    {
        if ($this->serializer) {
            throw new RuntimeException('I have already instantiated the serializer and can no longer set the serializer!');
        }
        $this->serializer = $serializer;
        return $this;
    }

    public function setSerializerCache(CacheItemPoolInterface $serializerCache): self
    {
        if ($this->serializerCache) {
            throw new RuntimeException('I have already instantiated the serializer cache and can no longer set the serializer cache!');
        }
        $this->serializerCache = $serializerCache;
        return $this;
    }

    public function setClassMetadataFactory(ClassMetadataFactoryInterface $classMetadataFactory): self
    {
        if ($this->classMetadataFactory) {
            throw new RuntimeException('I have already instantiated the class metadata factory and can no longer set it!');
        }
        $this->classMetadataFactory = $classMetadataFactory;
        return $this;
    }

    public function setPropertyConverter(NameConverterInterface $propertyConverter): self
    {
        if ($this->propertyConverter) {
            throw new RuntimeException('I have already instantiated the property converter and can no longer set the property converter!');
        }
        $this->propertyConverter = $propertyConverter;
        return $this;
    }

    public function setAdditionalNormalizers(array $additionalNormalizers): self
    {
        if (is_array($this->additionalNormalizers)) {
            throw new RuntimeException('I have already instantiated additional normalizers and can no longer set it!');
        }
        $this->additionalNormalizers = $additionalNormalizers;
        return $this;
    }

    public function setEncoders(array $encoders): self
    {
        if (is_array($this->encoders)) {
            throw new RuntimeException('I have already instantiated encoders and can no longer set it!');
        }
        $this->encoders = $encoders;
        return $this;
    }

    public function getEncoders(): array
    {
        if (!is_array($this->encoders)) {
            $this->encoders = [
                new XmlEncoder([XmlEncoder::ROOT_NODE_NAME => 'item']),
                new JsonEncoder(
                   new JsonEncode([JsonEncode::OPTIONS => JSON_UNESCAPED_SLASHES]),
                   new JsonDecode([JsonDecode::ASSOCIATIVE => false])
                )
            ];
        }
        return $this->encoders;
    }

    public function getApiResourceFacade(): ApiResourceFacade
    {
        if (!$this->apiResourceFacade) {
            $this->apiResourceFacade = new ApiResourceFacade(
                $this->getApiResourceRetriever(),
                $this->getApiResourcePersister(),
                $this->getClassResourceConverter(),
                $this->getSerializer(),
                $this->getFormatRetriever()
            );
        }
        return $this->apiResourceFacade;
    }

    public function getAdditionalNormalizers(): array
    {
        if (!is_array($this->additionalNormalizers)) {
            $this->additionalNormalizers = [];
        }
        return $this->additionalNormalizers;
    }

    public function setContainer(ContainerInterface $container): self
    {
        if ($this->container || $this->apiResourceFactory) {
            throw new RuntimeException('I have already instantiated services and can no longer set the container!');
        }
        $this->container = $container;
        return $this;
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    public function getFormatRetriever(): FormatRetriever
    {
        if (!$this->formatRetriever) {
            $this->formatRetriever = new FormatRetriever();
        }
        return $this->formatRetriever;
    }

    public function getSerializer(): SerializerInterface
    {
        if (!$this->serializer) {
            $normalizers = $this->getNormalizers();
            $encoders = $this->getEncoders();
            $this->serializer = new Serializer($normalizers, $encoders);
        }
        return $this->serializer;
    }

    /**
     * @return Reader
     */
    public function getAnnotationReader(): Reader
    {
        if (!$this->annotationReader) {
            AnnotationRegistry::registerLoader('class_exists');
            if (class_exists(PhpFileCache::class) && $this->getCacheFolder()) {
                $this->annotationReader = new CachedReader(
                    new AnnotationReader(),
                    new PhpFileCache($this->getCacheFolder()),
                    $this->isDebug()
                );
            } else {
                $this->annotationReader = new AnnotationReader();
            }
        }
        return $this->annotationReader;
    }

    public function getApiResourceMetadataFactory(): ApiResourceMetadataFactory
    {
        if (!$this->apiResourceMetadatafactory) {
            $this->apiResourceMetadatafactory = new ApiResourceMetadataFactory(
                $this->getAnnotationReader(),
                $this->getApiResourceFactory()
            );
        }
        return $this->apiResourceMetadatafactory;
    }

    public function getApiResourceFactory(): ApiResourceFactoryInterface
    {
        if (!$this->apiResourceFactory) {
            $this->apiResourceFactory = new ApiResourceFactory(
                $this->getContainer()
            );
        }
        return $this->apiResourceFactory;
    }

    public function getApiResourceRetriever(): ApiResourceRetriever
    {
        if (!$this->apiResourceRetriever) {
            $this->apiResourceRetriever = new ApiResourceRetriever(
                $this->getApiResourceMetadataFactory()
            );
        }
        return $this->apiResourceRetriever;
    }

    public function getApiResourcePersister(): ApiResourcePersister
    {
        if (!$this->apiResourcePersister) {
            $this->apiResourcePersister = new ApiResourcePersister(
                $this->getApiResourceMetadataFactory()
            );
        }
        return $this->apiResourcePersister;
    }

    public function getApiResources(): ApiResources
    {
        if (!$this->apiResources) {
            $this->apiResources = new ApiResources($this->apiResourceClasses);
        }
        return $this->apiResources;
    }

    public function getClassResourceConverter(): ClassResourceConverter
    {
        if (!$this->classResourceConverter) {
            $this->classResourceConverter = new ClassResourceConverter(
                $this->getPropertyConverter(),
                $this->getApiResources(),
                $this->isDebug()
            );
        }
        return $this->classResourceConverter;
    }

    public function getPropertyConverter(): NameConverterInterface
    {
        if (!$this->propertyConverter) {
            $this->propertyConverter = new CamelCaseToSnakeCaseNameConverter();
        }
        return $this->propertyConverter;
    }

    public function getClassMetadataFactory(): ClassMetadataFactoryInterface
    {
        if (!$this->classMetadataFactory) {
            $this->classMetadataFactory = new ClassMetadataFactory(
                new LoaderChain([
                    new AnnotationLoader($this->getAnnotationReader()),
                    new BaseGroupLoader(['read', 'write', 'get', 'post', 'put']),
                ])
            );
        }
        return $this->classMetadataFactory;
    }

    public function getSerializerCache(): CacheItemPoolInterface
    {
        if (!$this->serializerCache) {
            $this->serializerCache = new ArrayAdapter(0, true);
        }
        return $this->serializerCache;
    }

    public function getPropertyAccessor(): PropertyAccessor
    {
        if (!$this->propertyAccessor) {
            $this->propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
                ->setCacheItemPool($this->getSerializerCache())
                ->getPropertyAccessor();
        }
        return $this->propertyAccessor;
    }

    public function getPropertyTypeExtractor(): PropertyTypeExtractorInterface
    {
        if (!$this->propertyTypeExtractor) {
            $factory = $this->getClassMetadataFactory();
            $reflectionExtractor = new ReflectionExtractor();
            $phpDocExtractor = new PhpDocExtractor();

            $this->propertyTypeExtractor = new PropertyInfoExtractor(
                [
                    new SerializerExtractor($factory),
                    $reflectionExtractor,
                ],
                [
                    $phpDocExtractor,
                    $reflectionExtractor,
                ],
                [
                    $phpDocExtractor,
                ],
                [
                    $reflectionExtractor,
                ],
                [
                    $reflectionExtractor,
                ]
            );
        }
        return $this->propertyTypeExtractor;
    }

    public function getNormalizers(): array
    {
        if (!is_array($this->normalizers)) {
            $classMetadataFactory = $this->getClassMetadataFactory();
            $classDiscriminator = new ClassDiscriminatorFromClassMetadata($classMetadataFactory);

            $this->normalizers = $this->getAdditionalNormalizers();
            $this->normalizers[] = new ExceptionNormalizer($this->isDebug());
            $this->normalizers[] = new DateTimeNormalizer([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s']);

            if (class_exists(AbstractStringValueObject::class)) {
                $this->normalizers[] = StringValueObjectNormalizer::class;
            }

            $this->normalizers[] = new JsonSerializableNormalizer();
            $this->normalizers[] = new ArrayDenormalizer();

            $objectNormalizer = new ObjectNormalizer(
                $classMetadataFactory,
                $this->getPropertyConverter(),
                $this->getPropertyAccessor(),
                $this->getPropertyTypeExtractor(),
                $classDiscriminator,
                null,
                []
            );
            $evilObjectNormalizer = new EvilReflectionPropertyNormalizer(
                $classMetadataFactory,
                $this->getPropertyConverter(),
                $this->getPropertyAccessor(),
                $this->getPropertyTypeExtractor(),
                $classDiscriminator,
                null,
                []
            );
            $this->normalizers[] = new ContextualNormalizer([$evilObjectNormalizer]);
            $this->normalizers[] = $objectNormalizer;
        }
        return $this->normalizers;
    }

}
