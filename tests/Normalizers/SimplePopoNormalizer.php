<?php


namespace W2w\Test\Apie\Normalizers;


use ReflectionClass;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;
use W2w\Test\Apie\Mocks\Data\SimplePopo;

class SimplePopoNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface, SerializerAwareInterface
{
    private $normalizer;

    private $denormalizer;

    private $serializer;

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        $c = new SimplePopo();
        $reflClass = new ReflectionClass($c);
        $reflProp = $reflClass->getProperty('id');
        $reflProp->setAccessible(true);
        $reflProp->setValue($c, $data['id'] ?? null);

        $reflProp = $reflClass->getProperty('createdAt');
        $reflProp->setAccessible(true);
        $reflProp->setValue($c, $data['created_at'] ?? null);
        return $c;
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === SimplePopo::class;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        return [
            'id' => $object->getId(),
            'created_at' => $object->getCreatedAt()
        ];
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof SimplePopo;
    }

    public function setDenormalizer(DenormalizerInterface $denormalizer)
    {
        $this->denormalizer = $denormalizer;
    }

    public function getDenormalizer(): DenormalizerInterface
    {
        return $this->denormalizer;
    }

    public function setNormalizer(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    public function getNormalizer(): NormalizerInterface
    {
        return $this->normalizer;
    }

    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function getSerializer(): SerializerInterface
    {
        return $this->serializer;
    }
}
