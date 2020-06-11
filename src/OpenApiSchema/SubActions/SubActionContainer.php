<?php


namespace W2w\Lib\Apie\OpenApiSchema\SubActions;

use ReflectionMethod;

class SubActionContainer
{
    /**
     * @var object[][]
     */
    private $input;

    /**
     * @var SubActionFactory
     */
    private $subActionFactory;

    /**
     * @param object[][] $input
     */
    public function __construct(array $input, SubActionFactory $subActionFactory)
    {
        $this->input = $input;
        $this->subActionFactory = $subActionFactory;
    }

    public function getSubActionsForResourceClass(string $resourceClass): array
    {
        $res = [];
        foreach ($this->input as $actionName => $objects) {
            foreach ($objects as $object) {
                if (is_callable([$object, 'handle'])) {
                    $reflProp = new ReflectionMethod($object, 'handle');
                    $subAction = $this->subActionFactory->createFromReflectionMethod($actionName, $resourceClass, $reflProp, $object);
                    if ($subAction) {
                        $res[$actionName] = $subAction;
                    }
                }
            }
        }
        return $res;
    }
}
