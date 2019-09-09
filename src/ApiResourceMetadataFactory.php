<?php

namespace W2w\Lib\Apie;

use App\Annotations\ApiResource\ApiResource;
use App\Models\ApiResources\ApiResourceClassMetadata;
use Doctrine\Common\Annotations\Reader;
use ReflectionClass;
use RuntimeException;

class ApiResourceMetadataFactory
{
    private $reader;

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
            throw new RuntimeException('Class ' . $classNameOrInstance . ' has no ApiResource annotation.');
        }
        /** @var $annotation ApiResource */
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
