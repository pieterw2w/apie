<?php

namespace W2w\Lib\Apie\Plugins\Core\Serializers;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Serializer\Serializer;
use W2w\Lib\Apie\Events\DecodeEvent;
use W2w\Lib\Apie\Interfaces\FormatRetrieverInterface;
use W2w\Lib\Apie\Interfaces\ResourceSerializerInterface;
use W2w\Lib\Apie\PluginInterfaces\ResourceLifeCycleInterface;
use W2w\Lib\ApieObjectAccessNormalizer\ObjectAccess\ObjectAccess;
use Zend\Diactoros\Response\TextResponse;

/**
 * Wrapper around Symfony Serializer.
 *
 * @TODO: in version 4 move resourceLifeCycles to ApiResourceFacade.
 */
class SymfonySerializerAdapter implements ResourceSerializerInterface
{
    private $serializer;

    private $formatRetriever;

    private $resourceLifeCycles;

    /**
     * @param Serializer $serializer
     * @param FormatRetrieverInterface $formatRetriever
     * @param iterable<ResourceLifeCycleInterface> $resourceLifeCycles
     */
    public function __construct(
        Serializer $serializer,
        FormatRetrieverInterface $formatRetriever,
        iterable $resourceLifeCycles
    ) {
        $this->serializer = $serializer;
        $this->formatRetriever = $formatRetriever;
        $this->resourceLifeCycles = $resourceLifeCycles;
    }

    /**
     * Helper method to call the method on all all lifecycle instances.
     *
     * @param string $event
     * @param mixed[] $args
     */
    private function runLifeCycleEvent(string $event, ...$args)
    {
        foreach ($this->resourceLifeCycles as $resourceLifeCycle) {
            $resourceLifeCycle->$event(...$args);
        }
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
        $event = new DecodeEvent($requestBody, $contentType, $resource, get_class($resource));
        $this->runLifeCycleEvent('onPreDecodeRequestBody', $event);
        if (!$event->hasDecodedData()) {
            $event->setDecodedData($this->decodeRequestBody($requestBody, $contentType));
        }
        $this->runLifeCycleEvent('onPostDecodeRequestBody', $event);

        return $this->serializer->denormalize(
            $event->getDecodedData(),
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
    public function decodeRequestBody(string $requestBody, string $contentType)
    {
        $contentFormat = $this->formatRetriever->getFormat($contentType) ?? 'json';
        return $this->serializer->decode($requestBody, $contentFormat, []);
    }

    /**
     * {@inheritDoc}
     */
    public function postData(string $resourceClass, string $requestBody, string $contentType): object
    {
        $contentFormat = $this->formatRetriever->getFormat($contentType) ?? 'json';
        $event = new DecodeEvent($requestBody, $contentType, null, $resourceClass);
        $this->runLifeCycleEvent('onPreDecodeRequestBody', $event);
        if (!$event->hasDecodedData()) {
            $event->setDecodedData($this->decodeRequestBody($requestBody, $contentType));
        }
        $this->runLifeCycleEvent('onPostDecodeRequestBody', $event);
        return $this->serializer->denormalize(
            $event->getDecodedData(),
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
           ['object_access' => new ObjectAccess(false, true)]
        );
    }
}
