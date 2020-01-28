<?php


namespace W2w\Lib\Apie\Plugins\FakeAnnotations;

use Doctrine\Common\Annotations\Reader;
use W2w\Lib\Apie\PluginInterfaces\AnnotationReaderProviderInterface;
use W2w\Lib\Apie\PluginInterfaces\ApieAwareInterface;
use W2w\Lib\Apie\PluginInterfaces\ApieAwareTrait;
use W2w\Lib\Apie\Plugins\Core\CorePlugin;
use W2w\Lib\Apie\Plugins\FakeAnnotations\Readers\ExtendReaderWithConfigReader;

final class FakeAnnotationsPlugin implements AnnotationReaderProviderInterface, ApieAwareInterface
{
    use ApieAwareTrait;

    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getAnnotationReader(): Reader
    {
        $reader = $this->getApie()->getPlugin(CorePlugin::class)->getAnnotationReader();
        return new ExtendReaderWithConfigReader($reader, $this->config);
    }
}
