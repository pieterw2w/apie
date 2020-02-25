<?php

namespace W2w\Lib\Apie\Plugins\Core\PropertyInfo;

use ReflectionMethod;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor as SymfonyReflectionExtractor;

class ReflectionExtractorFactory
{
    public static function create(): SymfonyReflectionExtractor
    {
        $method = new ReflectionMethod(SymfonyReflectionExtractor::class, 'isReadable');
        $type = $method->getReturnType();
        if (null === $type) {
            return new ReflectionExtractorSerializer4();
        }
        return new ReflectionExtractor();
    }
}
