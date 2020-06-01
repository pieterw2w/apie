<?php
namespace W2w\Test\Apie\OpenApiSchema\Data;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use W2w\Lib\Apie\Annotations\ApiResource;
use W2w\Lib\Apie\Plugins\Core\DataLayers\NullDataLayer;
use W2w\Test\Apie\Mocks\ApiResources\SimplePopo;
use W2w\Test\Apie\OpenApiSchema\ValueObject;

/**
 * @ApiResource(persistClass=NullDataLayer::class)
 */
class MultipleTypesObject
{
    /**
     * @var float
     */
    public $floatingPoint;

    /**
     * @var double
     */
    public $double;

    /**
     * @var int
     */
    public $integer;

    /**
     * @var bool
     */
    public $boolean;

    /**
     * @var array
     */
    public $array;

    /**
     * @var string[]
     */
    public $stringArray;

    /**
     * @var SimplePopo[]
     */
    public $objectArray;

    /**
     * @var ValueObject
     */
    public $valueObject;

    /**
     * @SerializedName("name")
     * @var string
     */
    public $myMetadataIsADifferentName;

    /**
     * @Groups({"this-group-is-not-used"})
     * @var string
     */
    public $ignoredBecauseOfGroups;
}
