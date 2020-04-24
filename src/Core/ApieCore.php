<?php

namespace W2w\Lib\Apie\Core;

use erasys\OpenApi\Spec\v3\Document;
use W2w\Lib\Apie\Apie;
use W2w\Lib\Apie\Core\Resources\ApiResources;
use W2w\Lib\Apie\OpenApiSchema\OpenApiSchemaGenerator;
use W2w\Lib\Apie\OpenApiSchema\OpenApiSpecGenerator;
use W2w\Lib\Apie\OpenApiSchema\SchemaGenerator;
use W2w\Lib\Apie\PluginInterfaces\ResourceLifeCycleInterface;
use W2w\Lib\Apie\Plugins\Core\Normalizers\ApieObjectNormalizer;
use W2w\Lib\Apie\Plugins\Core\Normalizers\ContextualNormalizer;
use W2w\Lib\ApieObjectAccessNormalizer\ObjectAccess\ObjectAccess;

/**
 * Used by Apie to create the general Apie classes which you are not supposed to override in a plugin.
 *
 * @internal
 */
class ApieCore
{
    /**
     * @var Apie
     */
    private $apie;

    /**
     * @var PluginContainer
     */
    private $pluginContainer;

    /**
     * @var ApiResourceRetriever|null
     */
    private $retriever;

    /**
     * @var ApiResourcePersister|null
     */
    private $persister;

    /**
     * @var ApiResourceMetadataFactory|null
     */
    private $metadataFactory;

    /**
     * @var IdentifierExtractor|null
     */
    private $identifierExtractor;

    /**
     * @var SchemaGenerator|null
     */
    private $schemaGenerator;

    public function __construct(Apie $apie, PluginContainer $pluginContainer)
    {
        $this->apie = $apie;
        $this->pluginContainer = $pluginContainer;
    }

    public function getOpenApiSpecGenerator(): OpenApiSpecGenerator
    {
        return new OpenApiSpecGenerator(
            new ApiResources($this->apie->getResources()),
            $this->getClassResourceConverter(),
            $this->apie->createInfo(),
            $this->getSchemaGenerator(),
            $this->getApiResourceMetadataFactory(),
            $this->getIdentifierExtractor(),
            $this->apie->getBaseUrl(),
            function (Document $doc) {
                $this->apie->onOpenApiDocGenerated($doc);
            }
        );
    }

    public function getSchemaGenerator(): SchemaGenerator
    {
        if (!$this->schemaGenerator) {
            if (ContextualNormalizer::isNormalizerEnabled(ApieObjectNormalizer::class)) {
                $this->schemaGenerator = new SchemaGenerator(
                    $this->apie->getClassMetadataFactory(),
                    $this->apie->getPropertyTypeExtractor(),
                    $this->getClassResourceConverter(),
                    $this->apie->getPropertyConverter(),
                    $this->apie->getDynamicSchemaLogic()
                );
            } else {
                $this->schemaGenerator = new OpenApiSchemaGenerator(
                    $this->apie->getDynamicSchemaLogic(),
                    $this->apie->getObjectAccess(),
                    $this->apie->getClassMetadataFactory(),
                    $this->apie->getPropertyTypeExtractor(),
                    $this->getClassResourceConverter(),
                    $this->apie->getPropertyConverter()
                );
            }
            foreach ($this->apie->getDefinedStaticData() as $class => $schema) {
                $this->schemaGenerator->defineSchemaForResource($class, $schema);
            }

        }
        return $this->schemaGenerator;
    }

    public function getApiResourceFacade(): ApiResourceFacade
    {
        return new ApiResourceFacade(
            $this->getResourceRetriever(),
            $this->getResourcePersister(),
            $this->getClassResourceConverter(),
            $this->apie->getResourceSerializer(),
            $this->apie->getFormatRetriever(),
            $this->pluginContainer->getPluginsWithInterface(ResourceLifeCycleInterface::class)
        );
    }

    public function getResourceRetriever(): ApiResourceRetriever
    {
        if (!$this->retriever) {
            $this->retriever = new ApiResourceRetriever(
                $this->getApiResourceMetadataFactory()
            );
        }
        return $this->retriever;
    }

    public function getResourcePersister(): ApiResourcePersister
    {
        if (!$this->persister) {
            $this->persister = new ApiResourcePersister(
                $this->getApiResourceMetadataFactory()
            );
        }
        return $this->persister;
    }

    public function getApiResourceMetadataFactory(): ApiResourceMetadataFactory
    {
        if (!$this->metadataFactory) {
            $this->metadataFactory = new ApiResourceMetadataFactory(
                $this->apie->getAnnotationReader(),
                $this->apie->getApiResourceFactory()
            );
        }
        return $this->metadataFactory;
    }

    public function getIdentifierExtractor(): IdentifierExtractor
    {
        if (!$this->identifierExtractor) {
            $this->identifierExtractor = new IdentifierExtractor($this->apie->getPropertyAccessor());
        }
        return $this->identifierExtractor;
    }

    public function getClassResourceConverter(): ClassResourceConverter
    {
        return new ClassResourceConverter(
            $this->apie->getPropertyConverter(),
            new ApiResources($this->apie->getResources()),
            $this->apie->isDebug()
        );
    }
}
