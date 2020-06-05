<?php


namespace W2w\Test\Apie;


use PHPUnit\Framework\TestCase;
use ReflectionClass;
use W2w\Lib\Apie\Plugins\Core\Normalizers\ApieObjectNormalizer;
use W2w\Lib\Apie\Plugins\Core\Normalizers\ContextualNormalizer;

abstract class ForwardsCompatibleTestCase extends TestCase
{
    protected function setUp(): void
    {
        ContextualNormalizer::disableNormalizer(ApieObjectNormalizer::class);
    }

    protected function tearDown(): void
    {
        $this->hackCleanContextualNormalizer();
    }

    private function hackCleanContextualNormalizer()
    {
        $reflClass = new ReflectionClass(ContextualNormalizer::class);
        $prop = $reflClass->getProperty('globalDisabledNormalizers');
        $prop->setAccessible(true);
        $prop->setValue([]);

        $prop = $reflClass->getProperty('globalDisabledDenormalizers');
        $prop->setAccessible(true);
        $prop->setValue([]);
    }
}
