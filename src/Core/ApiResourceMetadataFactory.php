<?php

namespace W2w\Lib\Apie\Core;

use Doctrine\Common\Annotations\Reader;
use ReflectionClass;
use W2w\Lib\Apie\Annotations\ApiResource;
use W2w\Lib\Apie\Core\Models\ApiResourceClassMetadata;
use W2w\Lib\Apie\Exceptions\ApiResourceAnnotationNotFoundException;
use W2w\Lib\Apie\Interfaces\ApiResourceFactoryInterface;

/**
 * Creates Api Resource metadata using annotations on the class.
 */
class ApiResourceMetadataFactory
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var ApiResourceFactoryInterface
     */
    private $retrieverFactory;

    public function __construct(Reader $reader, ApiResourceFactoryInterface $retrieverFactory)
    {
        $this->reader = $reader;
        $this->retrieverFactory = $retrieverFactory;
    }

    public function getMetadata($classNameOrInstance): ApiResourceClassMetadata
    {
        $reflClass = new ReflectionClass($classNameOrInstance);
        $annotation = $this->reader->getClassAnnotation(
            $reflClass,
            ApiResource::class
        );
        if (!$annotation) {
            throw new ApiResourceAnnotationNotFoundException($classNameOrInstance);
        }
        /** @var ApiResource $annotation */
        $retriever = null;
        $persister = null;
        if ($annotation->retrieveClass) {
            $retriever = $this->retrieverFactory->getApiResourceRetrieverInstance($annotation->retrieveClass);
        }
        if ($annotation->persistClass) {
            $persister = $this->retrieverFactory->getApiResourcePersisterInstance($annotation->persistClass);
        }

        return new ApiResourceClassMetadata($classNameOrInstance, $annotation, $retriever, $persister);
    }
}
