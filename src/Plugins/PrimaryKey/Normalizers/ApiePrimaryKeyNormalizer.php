<?php

namespace W2w\Lib\Apie\Plugins\PrimaryKey\Normalizers;

use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;
use W2w\Lib\Apie\Core\ApiResourceMetadataFactory;
use W2w\Lib\Apie\Core\ClassResourceConverter;
use W2w\Lib\Apie\Core\IdentifierExtractor;
use W2w\Lib\Apie\Core\Resources\ApiResourcesInterface;
use W2w\Lib\Apie\Plugins\Core\Normalizers\ApieObjectNormalizer;
use W2w\Lib\Apie\Plugins\Core\Normalizers\ContextualNormalizer;
use W2w\Lib\Apie\Plugins\PrimaryKey\ValueObjects\PrimaryKeyReference;

/**
 * If a resource has an other api resource as a child, this class will map it as a url and not try to hydrate it as an url.
 */
class ApiePrimaryKeyNormalizer implements ContextAwareNormalizerInterface, SerializerAwareInterface
{
    use SerializerAwareTrait;

    /**
     * @var ApiResourcesInterface
     */
    private $apiResources;

    /**
     * @var IdentifierExtractor
     */
    private $identifierExtractor;

    /**
     * @var ApiResourceMetadataFactory
     */
    private $metadataFactory;

    /**
     * @var ClassResourceConverter
     */
    private $converter;

    /**
     * @var string
     */
    private $baseUrl;

    public function __construct(
        ApiResourcesInterface $apiResources,
        IdentifierExtractor $identifierExtractor,
        ApiResourceMetadataFactory $metadataFactory,
        ClassResourceConverter $converter,
        string $baseUrl
    ) {
        $this->apiResources = $apiResources;
        $this->identifierExtractor = $identifierExtractor;
        $this->metadataFactory = $metadataFactory;
        $this->converter = $converter;
        $this->baseUrl = rtrim($baseUrl, '/') . '/';
    }
    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        if (ContextualNormalizer::isNormalizerEnabled(ApieObjectNormalizer::class) || empty($context['object_hierarchy']) || !empty($context['disable_pk_normalize'])) {
            return false;
        }
        foreach ($this->apiResources->getApiResources() as $apiResource) {
            // if someone is really stupid to add this as an API resource....
            if ($apiResource === PrimaryKeyReference::class) {
                continue;
            }
            $resourceContext = $this->metadataFactory->getMetadata($apiResource)->getContext();
            $identifier = $this->identifierExtractor->getIdentifierKeyOfClass($apiResource, $resourceContext);
            if (null !== $identifier && is_a($data, $apiResource)) {
                return true;
            }
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $metadata = $this->metadataFactory->getMetadata($object);
        $resourceContext = $this->metadataFactory->getMetadata($object)->getContext();
        $identifierValue = $this->identifierExtractor->getIdentifierValue($object, $resourceContext);
        return $this->serializer->normalize(
            new PrimaryKeyReference(
                $metadata,
                $this->baseUrl . $this->converter->normalize($metadata->getClassName()),
                $identifierValue
            ),
            $format,
            $context
        );
    }
}
