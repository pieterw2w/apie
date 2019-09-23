<?php
namespace W2w\Lib\Apie;

use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Extracts the identifier from a resource.
 */
class IdentifierExtractor
{
    private $propertyAccessor;

    public function __construct(PropertyAccessor $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * Returns the name of the identifier of a resource. If it could not be determined,
     * it returns null.
     *
     * @param $resource
     * @param array $context
     * @return string|null
     */
    public function getIdentifierKey($resource, array $context = []): ?string
    {
        if (isset($context['identifier'])) {
            return $context['identifier'];
        }
        foreach (['id', 'uuid'] as $id) {
            if ($this->propertyAccessor->isReadable($resource, $id)) {
                return $id;
            }
        }
        return null;
    }

    /**
     * Return the value of the identifer of a resource.
     *
     * @param $resource
     * @param array $context
     * @return mixed|null
     */
    public function getIdentifierValue($resource, array $context = [])
    {
        $key = $this->getIdentifierKey($resource, $context);
        if (empty($key)) {
            return null;
        }
        return $this->propertyAccessor->getValue($resource, $key);
    }
}
