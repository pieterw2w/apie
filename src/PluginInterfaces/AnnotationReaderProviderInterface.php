<?php

namespace W2w\Lib\Apie\PluginInterfaces;

use Doctrine\Common\Annotations\Reader;

interface AnnotationReaderProviderInterface
{
    public function getAnnotationReader(): Reader;
}
