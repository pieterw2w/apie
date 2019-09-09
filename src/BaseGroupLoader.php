<?php

namespace W2w\Lib\Apie;

use Symfony\Component\Serializer\Mapping\ClassMetadataInterface;
use Symfony\Component\Serializer\Mapping\Loader\LoaderInterface;

class BaseGroupLoader implements LoaderInterface
{
    private $groups;

    public function __construct(array $groups)
    {
        $this->groups = $groups;
    }

    /**
     * @return bool
     */
    public function loadClassMetadata(ClassMetadataInterface $classMetadata)
    {
        foreach ($classMetadata->getAttributesMetadata() as $metadata) {
            if (empty($metadata->getGroups())) {
                foreach ($this->groups as $group) {
                    $metadata->addGroup($group);
                }
            }
        }
    }
}
