<?php
namespace W2w\Lib\Apie\Resources;

use HaydenPierce\ClassFinder\ClassFinder;
use W2w\Lib\Apie\Exceptions\BadConfigurationException;

/**
 * Returns all classes in a specific namespace. You require to install haydenpierce/class-finder with composer to get
 * this working.
 */
class ApiResourcesFromNamespace implements ApiResourcesInterface
{
    /**
     * @var string
     */
    private $namespace;

    public function __construct(string $namespace)
    {
        if (!class_exists(ClassFinder::class)) {
            throw new BadConfigurationException(__CLASS__ . ' can only be used if you require haydenpierce/class-finder in your project.');
        }
        $this->namespace = $namespace;
    }

    /**
     * @return string[]
     */
    public function getApiResources(): array
    {
        return ClassFinder::getClassesInNamespace($this->namespace);
    }
}
