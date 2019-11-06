<?php


namespace W2w\Lib\Apie\Normalizers;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

/**
 * Extension on DateTimeNormalizer to use Carbon over the regular DateTime class instances.
 */
class CarbonNormalizer extends DateTimeNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $type, $format = null, array $context = [])
    {
        $internalType = $type;
        if ($type === Carbon::class) {
            $internalType = DateTime::class;
        }
        if ($type === CarbonImmutable::class) {
            $internalType = DateTimeImmutable::class;
        }
        $result = parent::denormalize($data, $internalType, $format, $context);
        switch($type) {
            case Carbon::class:
            case DateTime::class:
                return Carbon::make($result);
            case DateTimeInterface::class:
                if (class_exists(CarbonImmutable::class)) {
                    return CarbonImmutable::make($result);
                }
                return Carbon::make($result);
            case CarbonImmutable::class:
            case DateTimeImmutable::class:
            if (class_exists(CarbonImmutable::class)) {
                return CarbonImmutable::make($result);
            }
            return $result;
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === Carbon::class || $type === CarbonImmutable::class || parent::supportsDenormalization($data, $type, $format);
    }

    /**
     * {@inheritdoc}
     */
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
