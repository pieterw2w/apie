<?php

namespace W2w\Lib\Apie\OpenApiSchema\SubActions;

use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionMethod;
use Symfony\Component\PropertyInfo\Type;

class SubAction
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var (Type|null)[]
     */
    private $arguments;

    /**
     * @var Type|null
     */
    private $returnTypehint;

    /**
     * @var ReflectionMethod
     */
    private $reflectionMethod;

    /**
     * @var object|null
     */
    private $object;

    /**
     * @param string $name
     * @param (Type|null)[] $arguments
     * @param ReflectionMethod $reflectionMethod
     * @param Type|null $returnTypehint
     * @param object|null $object
     */
    public function __construct(string $name, array $arguments, ReflectionMethod $reflectionMethod, ?Type $returnTypehint, ?object $object) {
        $this->name = $name;
        $this->arguments = $arguments;
        $this->reflectionMethod = $reflectionMethod;
        $this->returnTypehint = $returnTypehint;
        $this->object = $object;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function getSummary(): ?string
    {
        $factory  = DocBlockFactory::createInstance();
        $docComment = $this->reflectionMethod->getDocComment();
        if (!$docComment) {
            return null;
        }
        $docblock = $factory->create($docComment);
        return $docblock->getDescription() ? : null;
    }

    /**
     * @return Type[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @return Type|null
     */
    public function getReturnTypehint(): ?Type
    {
        return $this->returnTypehint;
    }

    /**
     * @return ReflectionMethod
     */
    public function getReflectionMethod(): ReflectionMethod
    {
        return $this->reflectionMethod;
    }

    /**
     * @return object|null
     */
    public function getObject(): ?object
    {
        return $this->object;
    }
}
