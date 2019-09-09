<?php

namespace W2w\Lib\Apie\Encoding;

class FormatRetriever
{
    public function getFormat(string $acceptHeader): string
    {
        if (strpos($acceptHeader, 'xml') !== false) {
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
