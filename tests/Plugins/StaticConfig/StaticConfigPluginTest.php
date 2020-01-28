<?php

namespace W2w\Test\Apie\Plugins\StaticConfig;

use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Apie;
use W2w\Lib\Apie\Plugins\StaticConfig\StaticConfigPlugin;

class StaticConfigPluginTest extends TestCase
{
    /**
     * @var Apie
     */
    private $apie;

    protected function setUp(): void
    {
        $this->apie = new Apie([new StaticConfigPlugin('http://google-api.nu/')], true, null);
    }

    public function testGetBaseUrl()
    {
        $this->assertEquals('http://google-api.nu/', $this->apie->getBaseUrl());
    }
}
