<?php
namespace W2w\Test\Apie\ApiResources;

use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\ApiResources\App;

class AppTest extends TestCase
{
    public function testGetters()
    {
        $testItem = new App('Unittest app', 'testing', '123456', true);
        $this->assertEquals('name', $testItem->getId());
        $this->assertEquals('Unittest app', $testItem->getAppName());
        $this->assertEquals('testing', $testItem->getEnvironment());
        $this->assertEquals('123456', $testItem->getHash());
        $this->assertEquals(true, $testItem->isDebug());
    }
}
