<?php
namespace W2w\Lib\Apie\Exceptions;

use W2w\Lib\ApieObjectAccessNormalizer\Exceptions\ApieException;
use W2w\Lib\ApieObjectAccessNormalizer\Exceptions\LocalizationableException;
use W2w\Lib\ApieObjectAccessNormalizer\Exceptions\LocalizationInfo;

class InvalidIdException extends ApieException implements LocalizationableException
{
    private $id;

    public function __construct(string $id)
    {
        $this->id = $id;
        parent::__construct(500, 'Id "' . $id . '" is not valid as identifier');
    }

    public function getI18n(): LocalizationInfo
    {
        return new LocalizationInfo(
            'validation.id',
            [
                'id' => $this->id
            ]
        );
    }
}
