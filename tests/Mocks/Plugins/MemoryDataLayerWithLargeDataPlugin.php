<?php


namespace W2w\Test\Apie\Mocks\Plugins;

use W2w\Lib\Apie\Interfaces\ApiResourceFactoryInterface;
use W2w\Lib\Apie\Interfaces\ApiResourcePersisterInterface;
use W2w\Lib\Apie\Interfaces\ApiResourceRetrieverInterface;
use W2w\Lib\Apie\PluginInterfaces\ApieAwareInterface;
use W2w\Lib\Apie\PluginInterfaces\ApieAwareTrait;
use W2w\Lib\Apie\PluginInterfaces\ApiResourceFactoryProviderInterface;
use W2w\Lib\Apie\Plugins\Core\DataLayers\MemoryDataLayer;
use W2w\Test\Apie\Mocks\ApiResources\FullRestObject;
use W2w\Test\Apie\OpenApiSchema\ValueObject;

class MemoryDataLayerWithLargeDataPlugin implements ApiResourceFactoryProviderInterface, ApieAwareInterface
{
    use ApieAwareTrait;

    private $factory;

    public function getApiResourceFactory(): ApiResourceFactoryInterface
    {
        if (!$this->factory) {
            $this->factory = new class implements ApiResourceFactoryInterface, ApieAwareInterface {
                use ApieAwareTrait;

                private $dataLayer;

                public function hasApiResourceRetrieverInstance(string $identifier): bool
                {
                    return $identifier === MemoryDataLayer::class;
                }

                /**
                 * Gets an instance of ApiResourceRetrieverInstance
                 * @param string $identifier
                 * @return ApiResourceRetrieverInterface
                 */
                public function getApiResourceRetrieverInstance(string $identifier): ApiResourceRetrieverInterface
                {
                    if (!$this->dataLayer) {
                        $this->loadDataLayer();
                    }
                    return $this->dataLayer;
                }

                /**
                 * Returns true if this factory can create this identifier.
                 *
                 * @param string $identifier
                 * @return bool
                 */
                public function hasApiResourcePersisterInstance(string $identifier): bool
                {
                    return $identifier === MemoryDataLayer::class;
                }

                /**
                 * Gets an instance of ApiResourceRetrieverInstance
                 * @param string $identifier
                 * @return ApiResourcePersisterInterface
                 */
                public function getApiResourcePersisterInstance(string $identifier): ApiResourcePersisterInterface
                {
                    if (!$this->dataLayer) {
                        $this->loadDataLayer();
                    }
                    return $this->dataLayer;
                }

                private function loadDataLayer()
                {
                    $access = $this->getApie()->getObjectAccess();
                    $this->dataLayer = new MemoryDataLayer($access, $this->getApie()->getIdentifierExtractor());
                    for ($i = 0; $i < 1000; $i++) {
                        $object = new FullRestObject();
                        $object->stringValue = 'value' . ($i % 20);
                        $object->valueObject = new ValueObject('pizza');
                        $this->dataLayer->persistNew($object);
                    }
                }
            };
            $this->factory->setApie($this->getApie());
        }
        return $this->factory;
    }
}
