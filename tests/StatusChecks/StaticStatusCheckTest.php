<?php
namespace W2w\Test\Apie\StatusChecks;

use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\ApiResources\Status;
use W2w\Lib\Apie\StatusChecks\StaticStatusCheck;

class StaticStatusCheckTest extends TestCase
{
    public function testGetters()
    {
        $testItem = new StaticStatusCheck(new Status('unit test'));
        $this->assertEquals(new Status('unit test'), $testItem->getStatus());
    }
}
