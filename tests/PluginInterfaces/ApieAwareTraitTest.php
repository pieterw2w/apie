<?php

namespace W2w\Test\Apie\PluginInterfaces;

use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Apie;
use W2w\Lib\Apie\Exceptions\BadConfigurationException;
use W2w\Lib\Apie\PluginInterfaces\ApieAwareTrait;

class ApieAwareTraitTest extends TestCase
{
    use ApieAwareTrait;

    protected function setUp(): void
    {
        $this->apie = null;
    }

    public function testHappyFlow()
    {
        $apie = new Apie([], false, null, false);
        $this->assertEquals($this, $this->setApie($apie));
        $this->assertSame($apie, $this->apie);
        $this->assertSame($apie, $this->getApie());
    }

    public function testSetApie_can_only_be_set_once()
    {
        $apie = new Apie([], false, null, false);
        $this->setApie($apie);
        $this->expectException(BadConfigurationException::class);
        $this->setApie(new Apie([], false, null, false));
        $this->assertSame($apie, $this->getApie());
    }

    public function testGetApie_throw_error_if_not_set()
    {
        $this->expectException(BadConfigurationException::class);
        $this->getApie();
    }
}
