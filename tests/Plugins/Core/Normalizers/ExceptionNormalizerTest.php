<?php
namespace W2w\Test\Apie\Plugins\Core\Normalizers;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use W2w\Lib\Apie\Plugins\Core\Normalizers\ExceptionNormalizer;

class ExceptionNormalizerTest extends TestCase
{
    public function testNormalizeWithStack()
    {
        $item = new ExceptionNormalizer(true);
        $this->assertFalse($item->supportsNormalization($this));
        $this->assertFalse($item->supportsNormalization(RuntimeException::class));
        $this->assertTrue($item->supportsNormalization(new RuntimeException("I am error")));
        $res = $item->normalize(new RuntimeException("I am error"));
        $this->assertArrayHasKey('trace', $res);
        unset($res['trace']);
        $this->assertEquals(
            [
                'type' => 'RuntimeException',
                'message' => 'I am error',
                'code' => 0
            ],
            $res
        );
    }

    public function testNormalizeWithoutStack()
    {
        $item = new ExceptionNormalizer(false);
        $this->assertFalse($item->supportsNormalization($this));
        $this->assertFalse($item->supportsNormalization(RuntimeException::class));
        $this->assertTrue($item->supportsNormalization(new RuntimeException("I am error")));
        $res = $item->normalize(new RuntimeException("I am error"));
        $this->assertEquals(
            [
                'type' => 'RuntimeException',
                'message' => 'I am error',
                'code' => 0
            ],
            $res
        );
    }
}
