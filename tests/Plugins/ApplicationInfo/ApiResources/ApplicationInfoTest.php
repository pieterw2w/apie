<?php
namespace W2w\Test\Apie\Plugins\ApplicationInfo\ApiResources;

use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Plugins\ApplicationInfo\ApiResources\ApplicationInfo;

class ApplicationInfoTest extends TestCase
{
    public function testGetters()
    {
        $testItem = new ApplicationInfo('Unittest app', 'testing', '123456', true);
        $this->assertEquals('Unittest app', $testItem->getAppName());
        $this->assertEquals('testing', $testItem->getEnvironment());
        $this->assertEquals('123456', $testItem->getHash());
        $this->assertEquals(true, $testItem->isDebug());
    }
}
