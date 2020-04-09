<?php

namespace W2w\Lib\Apie\Plugins\Core\Serializers;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Serializer\Serializer;
use W2w\Lib\Apie\Interfaces\FormatRetrieverInterface;
use W2w\Lib\Apie\Interfaces\ResourceSerializerInterface;
use W2w\Lib\ApieObjectAccessNormalizer\ObjectAccess\ObjectAccess;
use Zend\Diactoros\Response\TextResponse;

class SymfonySerializerAdapter implements ResourceSerializerInterface
{
    private $serializer;

    private $formatRetriever;

    public function __construct(Serializer $serializer, FormatRetrieverInterface $formatRetriever)
    {
        $this->serializer = $serializer;
        $this->formatRetriever = $formatRetriever;
    }

    /**
     * In case the symfony serializer is needed outside the adapter. It's highly discouraged to use
     * directly.
     *
     * @return Serializer
     */
    public function getSerializer(): Serializer
    {
        return $this->serializer;
    }

    /**
     * {@inheritDoc}
     */
    public function putData(object $resource, string $requestBody, string $contentType): object
    {
        $contentFormat = $this->formatRetriever->getFormat($contentType) ?? 'json';
        return $this->serializer->deserialize(
            $requestBody,
            get_class($resource),
            $contentFormat,
            [
                'groups' => ['base', 'write', 'put'],
                'object_to_populate' => $resource
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function postData(string $resourceClass, string $requestBody, string $contentType): object
    {
        $contentFormat = $this->formatRetriever->getFormat($contentType) ?? 'json';
        return $this->serializer->deserialize(
            $requestBody,
            $resourceClass,
            $contentFormat,
            [
                'groups' => ['base', 'write', 'post'],
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function toResponse($resource, string $acceptHeader): ResponseInterface
    {
        $format = $this->formatRetriever->getFormat($acceptHeader) ?? 'json';
        $contentType = $this->formatRetriever->getContentType($format);
        $response = $this->serializer->serialize($resource, $format, ['groups' => ['base', 'read', 'get']]);

        return new TextResponse($response, is_null($resource) ? 204 : 200, ['content-type' => $contentType]);
    }

    /**
     * {@inheritDoc}
     */
    public function normalize($resource, string $acceptHeader)
    {
        $format = $this->formatRetriever->getFormat($acceptHeader);
        return $this->serializer->normalize($resource, $format, ['groups' => ['base', 'read', 'get']]);
    }

    /**
     * {@inheritDoc}
     */
    public function hydrateWithReflection(array $data, string $resourceClass)
    {
        return $this->serializer->denormalize(
            $data,
            $resourceClass,
            null,
            ['disable_type_enforcement' => true, 'object_access' => new ObjectAccess(false)]
        );
    }
}
