<?php
namespace W2w\Test\Apie\Plugins\Carbon\Normalizers;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use W2w\Lib\Apie\Plugins\Carbon\Normalizers\CarbonNormalizer;

class CarbonNormalizerTest extends TestCase
{
    public function testDenormalize()
    {
        $testItem = new CarbonNormalizer([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s']);
        $this->assertEqualTime(
            new Carbon('1970-01-01 0:00:00'),
            $testItem->denormalize('1970-01-01 0:00:00', Carbon::class)
        );
        $this->assertEqualTime(
            new CarbonImmutable('1970-01-01 0:00:00'),
            $testItem->denormalize('1970-01-01 0:00:00', CarbonImmutable::class)
        );
        $this->assertEqualTime(
            new Carbon('1970-01-01 0:00:00'),
            $testItem->denormalize('1970-01-01 0:00:00', DateTime::class)
        );
        $this->assertEqualTime(
            new CarbonImmutable('1970-01-01 0:00:00'),
            $testItem->denormalize('1970-01-01 0:00:00', DateTimeImmutable::class)
        );
        $this->assertEqualTime(
            new CarbonImmutable('1970-01-01 0:00:00'),
            $testItem->denormalize('1970-01-01 0:00:00', DateTimeInterface::class)
        );
    }

    /**
     * https://github.com/sebastianbergmann/phpunit/issues/3948
     *
     * @param DateTimeInterface $expected
     * @param DateTimeInterface $actual
     */
    private function assertEqualTime(DateTimeInterface $expected, DateTimeInterface $actual)
    {
        $this->assertEquals($expected, $actual);
        $this->assertEquals(get_class($expected), get_class($actual));
    }
}
