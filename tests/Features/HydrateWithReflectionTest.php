<?php


namespace W2w\Test\Apie\Features;

use DateTime;
use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\DefaultApie;
use W2w\Test\Apie\Mocks\ApiResources\SimplePopo;

class HydrateWithReflectionTest extends TestCase
{
    public function testHydrateWithReflection()
    {
        $apie = DefaultApie::createDefaultApie();
        /** @var SimplePopo $actual */
        $actual = $apie->getResourceSerializer()->hydrateWithReflection(
            [
                'id' => 42,
                'created_at' => '1-1-1970',
            ],
            SimplePopo::class
        );
        $this->assertSame('42' , $actual->getId());
        $this->assertEquals(new DateTime('1-1-1970'), $actual->getCreatedAt());
    }
}
