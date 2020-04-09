<?php


namespace W2w\Lib\Apie\Exceptions;

use W2w\Lib\Apie\Interfaces\ApiResourcePersisterInterface;
use W2w\Lib\Apie\Interfaces\ApiResourceRetrieverInterface;
use W2w\Lib\ApieObjectAccessNormalizer\Exceptions\ApieException;

/**
 * Exception thrown if the persister or retriever did not return an instance of the resource we wanted.
 */
class InvalidReturnTypeOfApiResourceException extends ApieException
{
    /**
     * @param ApiResourceRetrieverInterface|ApiResourcePersisterInterface|null $retrieverOrPersister
     * @param string $identifier
     * @param string $expectedResource
     */
    public function __construct($retrieverOrPersister, string $identifier, string $expectedResource)
    {
        $message = 'I expect the class '
            . (is_null($retrieverOrPersister) ? '(null)' : get_class($retrieverOrPersister))
            . ' to return an instance of '
            . $expectedResource
            . ' but got '
            . $identifier;
        parent::__construct(500, $message);
    }
}
