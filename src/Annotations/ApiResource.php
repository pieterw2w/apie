<?php

namespace W2w\Lib\Apie\Annotations;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * Put this annotation on an API resource to configure how to persist/retrieve an API resource.
 *
 * @Annotation
 * @Target("CLASS")
 */
class ApiResource
{
    /**
     * @var string
     */
    public $persistClass;

    /**
     * @var string
     */
    public $retrieveClass;

    /**
     * @var array
     */
    public $context = [];

    /**
     * @var string[]
     */
    public $disabledMethods = [];

    /**
     * @param mixed[] $annotations
     * @return ApiResource
     */
    public static function createFromArray(array $annotations): self
    {
        $result = new self();
        foreach ($annotations as $key => $value) {
            $result->$key = $value;
        }
        return $result;
    }
}
