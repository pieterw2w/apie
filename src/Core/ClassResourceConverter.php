<?php
namespace W2w\Lib\Apie\Core;

use ReflectionClass;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use W2w\Lib\Apie\Core\Resources\ApiResourcesInterface;
use W2w\Lib\Apie\Exceptions\ResourceNameNotFoundException;

/**
 * Converts between the api resource name in the url and the class name used in the code.
 */
class ClassResourceConverter implements NameConverterInterface
{
    /**
     * @var NameConverterInterface
     */
    private $nameConverter;

    /**
     * @var ApiResourcesInterface
     */
    private $resources;

    /**
     * @var bool
     */
    private $showResources;

    /**
     * @param NameConverterInterface $nameConverter
     * @param ApiResourcesInterface $resources
     * @param bool $showResources
     */
    public function __construct(NameConverterInterface $nameConverter, ApiResourcesInterface $resources, bool $showResources = true)
    {
        $this->nameConverter = $nameConverter;
        $this->resources = $resources;
        $this->showResources = $showResources;
    }

    /**
     * Converts a property name to its normalized value.
     *
     * @param string $propertyName
     *
     * @return string
     */
    public function normalize($propertyName)
    {
        $class = new ReflectionClass($propertyName);

        return $this->nameConverter->normalize($class->getShortName());
    }

    /**
     * Converts a property name to its denormalized value.
     *
     * @param string $propertyName
     *
     * @return string
     */
    public function denormalize($propertyName)
    {
        $available = [];
        $search = ucfirst($this->nameConverter->denormalize($propertyName));
        foreach ($this->resources->getApiResources() as $className) {
            $class = new ReflectionClass($className);
            if ($class->getShortName() === $search) {
                return $className;
            }
            if ($this->showResources) {
                $available[] = $this->nameConverter->normalize($class->getShortName());
            }
        }
        $availableMsg = '';
        if ($this->showResources) {
            $availableMsg = 'Possible resources: ' . implode(', ', $available);
        }
        throw new ResourceNameNotFoundException($propertyName, $availableMsg);
    }
}
