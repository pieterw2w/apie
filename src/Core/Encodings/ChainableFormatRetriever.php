<?php


namespace W2w\Lib\Apie\Core\Encodings;

use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use W2w\Lib\Apie\Interfaces\FormatRetrieverInterface;

class ChainableFormatRetriever implements FormatRetrieverInterface
{
    /**
     * @var FormatRetrieverInterface[]
     */
    private $formatRetrievers;

    /**
     * @param FormatRetrieverInterface[]
     */
    public function __construct(array $formatRetrievers)
    {
        $this->formatRetrievers = $formatRetrievers;
    }

    /**
     * @param string $contentType
     * @return string|null
     */
    public function getFormat(string $contentType): ?string
    {
        $contentTypes = explode(',', $contentType);
        foreach ($contentTypes as $individualContentType) {
            if (strpos($individualContentType, ';') !== false) {
                $individualContentType = strstr($individualContentType, ';', true);
            }
            $res = $this->findOne('getFormat', $individualContentType);
            if (!is_null($res)) {
                return $res;
            }
        }
        throw new NotAcceptableHttpException('"' . $contentType . '" is not accepted');
    }

    /**
     * @param string $format
     * @return string|null
     */
    public function getContentType(string $format): ?string
    {
        $res = $this->findOne('getContentType', $format);
        if (!is_null($res)) {
            return $res;
        }
        throw new NotAcceptableHttpException('"' . $format . '" is not accepted');
    }

    /**
     * Helper method or getFormat and getContentType.
     *
     * @param string $method
     * @param string $input
     * @return string|null
     */
    private function findOne(string $method, string $input): ?string
    {
        foreach ($this->formatRetrievers as $formatRetriever) {
            $res = $formatRetriever->$method($input);
            if (!is_null($res)) {
                return $res;
            }
        }
        return null;
    }
}
