<?php
namespace W2w\Lib\Apie\Normalizers;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UuidNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function denormalize($data, $type, $format = null, array $context = [])
    {
        return Uuid::fromString($data);
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return (Uuid::class === $type || UuidInterface::class === $type) && is_string($data);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return $object->toString();
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof UuidInterface;
    }
}
