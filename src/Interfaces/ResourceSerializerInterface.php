<?php


namespace W2w\Lib\Apie\Interfaces;

use Psr\Http\Message\ResponseInterface;

/**
 * @TODO add method decodeRequestBody and make $requestBody accept array in version 4.
 */
interface ResourceSerializerInterface
{
    /**
     * Serializes a request body to an existing resource.
     *
     * @param object $resource
     * @param string $requestBody
     * @param string $contentType
     * @return object
     */
    public function putData(object $resource, string $requestBody, string $contentType): object;

    /**
     * Serializes a request body to a new resource using resource class mentioned in $resourceClass
     * @param string $resourceClass
     * @param string $requestBody
     * @param string $contentType
     * @return object
     */
    public function postData(string $resourceClass, string $requestBody, string $contentType): object;

    /**
     * Serializes a resource to a response.
     *
     * @param object|array|null $resource
     * @param string $acceptHeader
     * @return ResponseInterface
     */
    public function toResponse($resource, string $acceptHeader): ResponseInterface;

    /**
     * Serializes a resource to primitives (which can be encoded to xml/json, etc).
     *
     * @param object|array|null $resource
     * @param string $acceptHeader
     * @return mixed
     */
    public function normalize($resource, string $acceptHeader);

    /**
     * Hydrates an array to a resource class using reflection if setters are not provided.
     * Often this is used internally by data layers to map an object with no provided setter.
     *
     * @param array $data
     * @param string $resourceClass
     * @return object|array
     */
    public function hydrateWithReflection(array $data, string $resourceClass);
}
