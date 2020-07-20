<?php
namespace W2w\Lib\Apie\Exceptions;

use ReflectionClass;
use W2w\Lib\ApieObjectAccessNormalizer\Exceptions\LocalizationableException;
use W2w\Lib\ApieObjectAccessNormalizer\Exceptions\LocalizationInfo;

class InvalidValueForValueObjectException extends ApieException implements LocalizationableException
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var mixed
     */
    private $value;

    public function __construct($value, $valueObject)
    {
        $refl = new ReflectionClass($valueObject);
        $this->name = strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $refl->getShortName()));
        $this->value = $value;
        parent::__construct(
            422,
            '"' . $value . '" is not a valid value for value object ' . $this->name
        );
    }

    public function getI18n(): LocalizationInfo
    {
        return new LocalizationInfo(
            'validation.format',
            [
                'name' => $this->name,
                'value' => $this->value,
            ]
        );
    }
}
