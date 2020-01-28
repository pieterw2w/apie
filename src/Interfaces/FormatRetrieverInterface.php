<?php


namespace W2w\Lib\Apie\Interfaces;

/**
 * Converts between the format string used for the symfony serializer and the request/response header to indicate
 * the mime type.
 */
interface FormatRetrieverInterface
{
    /**
     * @param string $contentType
     * @return string|null
     */
    public function getFormat(string $contentType): ?string;

    /**
     * @param string $format
     * @return string|null
     */
    public function getContentType(string $format): ?string;
}
