<?php


namespace W2w\Lib\Apie\Normalizers;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

/**
 * Extension on DateTimeNormalizer to use Carbon over the regular DateTime class instances.
 */
class CarbonNormalizer extends DateTimeNormalizer
{
    private $allowedTypes = [
        Carbon::class,
        CarbonInterface::class
    ];

    private $before = [
        CarbonInterface::class => DateTimeInterface::class,
        Carbon::class => DateTime::class,
    ];

    private $after = [
        DateTime::class => Carbon::class,
        DateTimeInterface::class => Carbon::class,
        Carbon::class => Carbon::class,
        CarbonInterface::class => Carbon::class,
    ];

    public function __construct($defaultContext = [], DateTimeZone $timezone = null)
    {
        parent::__construct($defaultContext, $timezone);
        // carbon 2 support.
        if (class_exists(CarbonImmutable::class)) {
            $this->allowedTypes[] = CarbonImmutable::class;
            $this->before[CarbonImmutable::class] = DateTimeImmutable::class;
            $this->after[DateTimeInterface::class] = CarbonImmutable::class;
            $this->after[CarbonInterface::class] = CarbonImmutable::class;
            $this->after[CarbonImmutable::class] = CarbonImmutable::class;
            $this->after[DateTimeImmutable::class] = CarbonImmutable::class;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $type, $format = null, array $context = [])
    {
        $internalType = $type;
        if (isset($this->before[$type])) {
            $internalType = $this->before[$type];
        }
        $result = parent::denormalize($data, $internalType, $format, $context);
        if (isset($this->after[$type])) {
            return $this->after[$type]::make($result);
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return in_array($type, $this->allowedTypes) || parent::supportsDenormalization($data, $type, $format);
    }

    /**
     * {@inheritdoc}
     */
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
