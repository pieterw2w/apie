<?php

namespace W2w\Lib\Apie\PluginInterfaces;

use erasys\OpenApi\Spec\v3\Schema;

interface SchemaProviderInterface
{
    /**
     * @return Schema[]
     */
    public function getDefinedStaticData(): array;

    /**
     * @return callable[]
     */
    public function getDynamicSchemaLogic(): array;
}
