<?php

namespace W2w\Lib\Apie;

use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use UnexpectedValueException;

class ApiResourceRetriever
{
    private $factory;

    public function __construct(ApiResourceMetadataFactory $factory)
    {
        $this->factory = $factory;
    }

    public function retrieve(string $resourceClass, $id)
    {
        $metadata = $this->factory->getMetadata($resourceClass);
        if (!$metadata->allowGet()) {
            throw new MethodNotAllowedHttpException([], '"Resource has no get $id"');
        }
        $result = $metadata->getResourceRetriever()
            ->retrieve($resourceClass, $id, $metadata->getContext());
        if (!$result instanceof $resourceClass) {
            throw new UnexpectedValueException('I expect the class ' . get_class($metadata->getResourceRetriever()) . ' to return an instance of ' . $resourceClass . ' but got ' . $this->getType($result));
        }

        return $result;
    }

    public function retrieveAll(string $resourceClass, int $pageIndex, int $numberOfItems)
    {
        $metadata = $this->factory->getMetadata($resourceClass);
        if (!$metadata->allowGetAll()) {
            throw new MethodNotAllowedHttpException([], '"Resource has no get all"');
        }
        if (!$metadata->hasResourceRetriever()) {
            // Many OpenAPI generators expect the get all call to be working at all times.
            return [];
        }
        $result = $metadata->getResourceRetriever()
            ->retrieveAll($resourceClass, $metadata->getContext(), $pageIndex, $numberOfItems);
        foreach ($result as $instance) {
            if (!$instance instanceof $resourceClass) {
                throw new UnexpectedValueException('I expect the class ' . get_class($metadata->getResourceRetriever()) . ' to return a list of instances of ' . $resourceClass . ' but got ' . $this->getType($instance));
            }
        }

        return $result;
    }

    private function getType($object)
    {
        if (is_object($object)) {
            return get_class($object);
        }
        if (is_string($object)) {
            return 'string ' . json_encode($object);
        }

        return gettype($object);
    }
}
