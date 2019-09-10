<?php
namespace W2w\Test\Apie\ApiResources;

use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\ApiResources\Status;

class StatusTest extends TestCase
{
    public function testGetters()
    {
        $testItem = new Status('status name', 'OK', 'https://fake-hosting/', null);
        $this->assertEquals('status name', $testItem->getId());
        $this->assertEquals('OK', $testItem->getStatus());
        $this->assertEquals('https://fake-hosting/', $testItem->getOptionalReference());
        $this->assertNull($testItem->getContext());
    }

    public function testHasNoErrors()
    {
        $testItem = new Status('status', 'OK');
        $this->assertTrue($testItem->hasNoErrors());
        $testItem = new Status('status', 'Database is down!');
        $this->assertFalse($testItem->hasNoErrors());
    }
}
