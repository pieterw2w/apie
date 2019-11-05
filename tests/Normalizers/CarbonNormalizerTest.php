<?php
namespace W2w\Test\Apie\Normalizers;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use W2w\Lib\Apie\Normalizers\CarbonNormalizer;

class CarbonNormalizerTest extends TestCase
{
    public function testDenormalize()
    {
        $testItem = new CarbonNormalizer([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s']);
        $this->assertEquals(
            new Carbon('1970-01-01 0:00:00'),
            $testItem->denormalize('1970-01-01 0:00:00', Carbon::class)
        );
        $this->assertEquals(
            new CarbonImmutable('1970-01-01 0:00:00'),
            $testItem->denormalize('1970-01-01 0:00:00', CarbonImmutable::class)
        );
        $this->assertEquals(
            new Carbon('1970-01-01 0:00:00'),
            $testItem->denormalize('1970-01-01 0:00:00', DateTime::class)
        );
        $this->assertEquals(
            new CarbonImmutable('1970-01-01 0:00:00'),
            $testItem->denormalize('1970-01-01 0:00:00', DateTimeImmutable::class)
        );
        $this->assertEquals(
            new CarbonImmutable('1970-01-01 0:00:00'),
            $testItem->denormalize('1970-01-01 0:00:00', DateTimeInterface::class)
        );

        $class = new class('1970-01-01 0:00:00') extends DateTime{};

        $this->assertEquals(
            $class,
            $testItem->denormalize('1970-01-01 0:00:00', get_class($class))
        );
    }
}
