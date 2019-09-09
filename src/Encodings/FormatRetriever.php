<?php

namespace W2w\Lib\Apie\Encodings;

/**
 * Converts between the format string used for the symfony serializer and the request/response header to indicate
 * the mime type.
 *
 * @TODO: make this class extendable and less hardcoded.
 */
class FormatRetriever
{
    /**
     * @param string $contentType
     * @return string
     */
    public function getFormat(string $contentType): string
    {
        if (strpos($contentType, 'xml') !== false) {
            return 'xml';
        }

        return 'json';
    }

    public function getContentType(string $format): string
    {
        if ($format === 'xml') {
            return 'application/xml';
        }

        return 'application/json';
    }
}
