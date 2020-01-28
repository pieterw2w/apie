<?php
namespace W2w\Lib\Apie\Core\Resources;

use HaydenPierce\ClassFinder\ClassFinder;
use W2w\Lib\Apie\Exceptions\BadConfigurationException;
use W2w\Lib\Apie\Plugins\ApplicationInfo\ApiResources\ApplicationInfo;
use W2w\Lib\Apie\Plugins\StatusCheck\ApiResources\Status;

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
        // @codeCoverageIgnoreStart
        if (!class_exists(ClassFinder::class)) {
            throw new BadConfigurationException(__CLASS__ . ' can only be used if you require haydenpierce/class-finder in your project.');
        }
        // @codeCoverageIgnoreEnd
        $this->namespace = $namespace;
    }

    /**
     * Returns all api resources.
     *
     * @return string[]
     */
    public function getApiResources(): array
    {
        return ClassFinder::getClassesInNamespace($this->namespace);
    }

    public static function createApiResources(string $namespace, bool $defaultResources = true): array
    {
        // @codeCoverageIgnoreStart
        if (!class_exists(ClassFinder::class)) {
            throw new BadConfigurationException(__CLASS__ . ' can only be used if you require haydenpierce/class-finder in your project.');
        }
        // @codeCoverageIgnoreEnd
        $classes = ClassFinder::getClassesInNamespace($namespace);
        if ($defaultResources) {
            $classes[] = ApplicationInfo::class;
            $classes[] = Status::class;
        }
        return $classes;
    }
}
