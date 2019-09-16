<?php
namespace W2w\Test\Apie\OpenApiSchema\Data;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use W2w\Test\Apie\Mocks\Data\SimplePopo;

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
