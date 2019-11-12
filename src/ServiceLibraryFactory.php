<?php
namespace W2w\Lib\Apie;

use Carbon\Carbon;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\PhpFileCache;
use erasys\OpenApi\Spec\v3\Info;
use GBProd\UuidNormalizer\UuidDenormalizer;
use GBProd\UuidNormalizer\UuidNormalizer;
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
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use W2w\Lib\Apie\ApiResources\App;
use W2w\Lib\Apie\ApiResources\Status;
use W2w\Lib\Apie\Encodings\FormatRetriever;
use W2w\Lib\Apie\Normalizers\ApieObjectNormalizer;
use W2w\Lib\Apie\Normalizers\CarbonNormalizer;
use W2w\Lib\Apie\Normalizers\ContextualNormalizer;
use W2w\Lib\Apie\Normalizers\EvilReflectionPropertyNormalizer;
use W2w\Lib\Apie\Normalizers\ExceptionNormalizer;
use W2w\Lib\Apie\Normalizers\StringValueObjectNormalizer;
use W2w\Lib\Apie\Normalizers\ValueObjectNormalizer;
use W2w\Lib\Apie\OpenApiSchema\OpenApiSpecGenerator;
use W2w\Lib\Apie\OpenApiSchema\SchemaGenerator;
use W2w\Lib\Apie\Resources\ApiResources;
use W2w\Lib\Apie\Resources\ApiResourcesInterface;

/**
 * To avoid lots of boilerplate in using the library, this class helps in making sensible defaults.
 * @codeCoverageIgnore
 */
class ServiceLibraryFactory
{
    /**
     * @var boolean
     */
    private $debug;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ApiResourceFacade
     */
    private $apiResourceFacade;

    /**
     * @var ApiResourcesInterface
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
     * @var (NormalizerInterface|DenormalizerInterface)[]|null
     */
    private $normalizers;

    /**
     * @var (NormalizerInterface|DenormalizerInterface)[]|null
     */
    private $additionalNormalizers;

    /**
     * @var EncoderInterface[]|null
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
     * @var Info
     */
    private $info;

    /**
     * @var SchemaGenerator
     */
    private $schemaGenerator;

    /**
     * @var OpenApiSpecGenerator
     */
    private $openApiSpecGenerator;

    /**
     * @var callable[]
     */
    private $callables = [];

    /**
     * @param string[]|ApiResourcesInterface $apiResourceClasses
     * @param bool $debug
     * @param string|null $cacheFolder
     */
    public function __construct($apiResourceClasses = [App::class, Status::class], bool $debug = false, ?string $cacheFolder = null)
    {
        $this->apiResources = $apiResourceClasses instanceof ApiResourcesInterface ? $apiResourceClasses : new ApiResources($apiResourceClasses);
        $this->debug = $debug;
        $this->cacheFolder = $cacheFolder;
    }

    private function isDebug(): bool
    {
        return $this->debug;
    }

    private function getCacheFolder(): ?string
    {
        return $this->cacheFolder;
    }

    /**
     * Workaround to run a callable to set some values when the Serializer is being instantiated.
     *
     * @param callable $callable
     * @return ServiceLibraryFactory
     */
    public function runBeforeInstantiation(callable $callable): self
    {
        $this->callables[] = $callable;
        return $this;
    }

    public function setApiResourceFactory(ApiResourceFactoryInterface $apiResourceFactory): self
    {
        if ($this->apiResourceFactory) {
            throw new RuntimeException('I have already instantiated ApiResourceFactory and can no longer set it!');
        }
        $this->apiResourceFactory = $apiResourceFactory;
        return $this;
    }

    public function setInfo(Info $info): self
    {
        if ($this->info) {
            throw new RuntimeException('I have already instantiated Info and can no longer set it!');
        }
        $this->info = $info;
        return $this;
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

    private function getEncoders(): array
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

    private function getAdditionalNormalizers(): array
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

    private function getContainer(): ?ContainerInterface
    {
        return $this->container;
    }

    private function getFormatRetriever(): FormatRetriever
    {
        if (!$this->formatRetriever) {
            $this->formatRetriever = new FormatRetriever();
        }
        return $this->formatRetriever;
    }

    public function getSerializer(): SerializerInterface
    {
        if (!$this->serializer) {
            foreach ($this->callables as $callable) {
                $callable('serializer');
            }
            $normalizers = $this->getNormalizers();
            $encoders = $this->getEncoders();
            $this->serializer = new Serializer($normalizers, $encoders);
        }
        return $this->serializer;
    }

    /**
     * @return Reader
     */
    private function getAnnotationReader(): Reader
    {
        if (!$this->annotationReader) {
            /** @scrutinizer ignore-deprecated */AnnotationRegistry::registerLoader('class_exists');
            if (class_exists(PhpFileCache::class) && $this->getCacheFolder()) {
                $this->annotationReader = new CachedReader(
                    new AnnotationReader(),
                    new PhpFileCache($this->getCacheFolder() . DIRECTORY_SEPARATOR . '/doctrine-cache'),
                    $this->isDebug()
                );
            } else {
                $this->annotationReader = new AnnotationReader();
            }
        }
        return $this->annotationReader;
    }

    private function getApiResourceMetadataFactory(): ApiResourceMetadataFactory
    {
        if (!$this->apiResourceMetadatafactory) {
            $this->apiResourceMetadatafactory = new ApiResourceMetadataFactory(
                $this->getAnnotationReader(),
                $this->getApiResourceFactory()
            );
        }
        return $this->apiResourceMetadatafactory;
    }

    private function getApiResourceFactory(): ApiResourceFactoryInterface
    {
        if (!$this->apiResourceFactory) {
            $this->apiResourceFactory = new ApiResourceFactory(
                $this->getContainer()
            );
        }
        return $this->apiResourceFactory;
    }

    private function getApiResourceRetriever(): ApiResourceRetriever
    {
        if (!$this->apiResourceRetriever) {
            $this->apiResourceRetriever = new ApiResourceRetriever(
                $this->getApiResourceMetadataFactory()
            );
        }
        return $this->apiResourceRetriever;
    }

    private function getApiResourcePersister(): ApiResourcePersister
    {
        if (!$this->apiResourcePersister) {
            $this->apiResourcePersister = new ApiResourcePersister(
                $this->getApiResourceMetadataFactory()
            );
        }
        return $this->apiResourcePersister;
    }

    public function getApiResources(): ApiResourcesInterface
    {
        return $this->apiResources;
    }

    private function getClassResourceConverter(): ClassResourceConverter
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

    private function getPropertyConverter(): NameConverterInterface
    {
        if (!$this->propertyConverter) {
            $this->propertyConverter = new CamelCaseToSnakeCaseNameConverter();
        }
        return $this->propertyConverter;
    }

    private function getClassMetadataFactory(): ClassMetadataFactoryInterface
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

    private function getSerializerCache(): CacheItemPoolInterface
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

    private function getPropertyTypeExtractor(): PropertyTypeExtractorInterface
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

    private function getNormalizers(): array
    {
        if (!is_array($this->normalizers)) {
            $classMetadataFactory = $this->getClassMetadataFactory();
            $classDiscriminator = new ClassDiscriminatorFromClassMetadata($classMetadataFactory);

            $this->normalizers = $this->getAdditionalNormalizers();
            $this->normalizers[] = new ExceptionNormalizer($this->isDebug());

            if (class_exists(Carbon::class)) {
                $this->normalizers[] = new CarbonNormalizer([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s']);
            } else {
                $this->normalizers[] = new DateTimeNormalizer([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s']);
            }
            $this->normalizers[] = new ValueObjectNormalizer();
            $this->normalizers[] = new UuidNormalizer();
            $this->normalizers[] = new UuidDenormalizer();

            if (class_exists(AbstractStringValueObject::class)) {
                $this->normalizers[] = new StringValueObjectNormalizer();
            }

            $this->normalizers[] = new JsonSerializableNormalizer();
            $this->normalizers[] = new ArrayDenormalizer();

            $objectNormalizer = new ApieObjectNormalizer(
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
            ContextualNormalizer::disableDenormalizer(EvilReflectionPropertyNormalizer::class);
            ContextualNormalizer::disableNormalizer(EvilReflectionPropertyNormalizer::class);
        }
        return $this->normalizers;
    }

    private function getInfo(): Info
    {
        if (!$this->info) {
            $this->info = new Info('', '');
        }
        return $this->info;
    }

    public function getSchemaGenerator(): SchemaGenerator
    {
        if (!$this->schemaGenerator) {
            $this->schemaGenerator = new SchemaGenerator(
                $this->getClassMetadataFactory(),
                $this->getPropertyTypeExtractor(),
                $this->getClassResourceConverter(),
                $this->getPropertyConverter()
            );
        }
        return $this->schemaGenerator;
    }

    public function getOpenApiSpecGenerator(string $baseUrl): OpenApiSpecGenerator
    {
        if (!$this->openApiSpecGenerator) {
            $this->openApiSpecGenerator = new OpenApiSpecGenerator(
                $this->getApiResources(),
                $this->getClassResourceConverter(),
                $this->getInfo(),
                $this->getSchemaGenerator(),
                $this->getApiResourceMetadataFactory(),
                $baseUrl
            );
        }
        return $this->openApiSpecGenerator;
    }
}
