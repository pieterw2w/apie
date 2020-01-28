<?php

namespace W2w\Lib\Apie\Plugins\StatusCheck\DataLayers;

use CallbackFilterIterator;
use Generator;
use LimitIterator;
use RewindableGenerator;
use W2w\Lib\Apie\Core\Models\ApiResourceClassMetadata;
use W2w\Lib\Apie\Core\SearchFilters\PhpPrimitive;
use W2w\Lib\Apie\Core\SearchFilters\SearchFilter;
use W2w\Lib\Apie\Core\SearchFilters\SearchFilterRequest;
use W2w\Lib\Apie\Exceptions\InvalidClassTypeException;
use W2w\Lib\Apie\Exceptions\ResourceNotFoundException;
use W2w\Lib\Apie\Interfaces\ApiResourceRetrieverInterface;
use W2w\Lib\Apie\Interfaces\SearchFilterProviderInterface;
use W2w\Lib\Apie\Plugins\StatusCheck\ApiResources\Status;
use W2w\Lib\Apie\Plugins\StatusCheck\StatusChecks\StatusCheckInterface;
use W2w\Lib\Apie\Plugins\StatusCheck\StatusChecks\StatusCheckListInterface;

/**
 * Status check retriever retrieves instances of Status. A status check needs to implement StatusCheckInterface
 * or StatusCheckListInterface and sent in the constructor of this method.
 */
class StatusCheckRetriever implements ApiResourceRetrieverInterface, SearchFilterProviderInterface
{
    private $statusChecks;

    /**
     * @param (StatusCheckInterface|StatusCheckListInterface)[] $statusChecks
     */
    public function __construct(iterable $statusChecks)
    {
        $this->statusChecks = $statusChecks;
    }

    /**
     * Iterates over all status checks and creates a generator for it.
     *
     * @return Generator
     */
    private function iterate(): Generator
    {
        foreach ($this->statusChecks as $statusCheck) {
            $check = false;
            if ($statusCheck instanceof StatusCheckInterface) {
                $check = true;
                yield $statusCheck->getStatus();
            }
            if ($statusCheck instanceof StatusCheckListInterface) {
                $check = true;
                foreach ($statusCheck as $check) {
                    if ($check instanceof Status) {
                        yield $check;
                    } else if ($check instanceof StatusCheckInterface) {
                        yield $check->getStatus();
                    } else {
                        throw new InvalidClassTypeException(get_class($check), 'StatusCheckInterface or Status');
                    }
                }
            }
            if (!$check) {
                throw new InvalidClassTypeException(get_class($statusCheck), 'StatusCheckInterface or StatusCheckListInterface');
            }
        }
    }

    /**
     * Finds the correct status check or throw a 404 if it could not be found.
     *
     * @param string $resourceClass
     * @param mixed $id
     * @param array $context
     * @return Status
     */
    public function retrieve(string $resourceClass, $id, array $context)
    {
        foreach ($this->iterate() as $statusCheck) {
            if ($statusCheck->getId() === $id) {
                return $statusCheck;
            }
        }
        throw new ResourceNotFoundException($id);
    }

    /**
     * Return all status check results.
     *
     * @param string $resourceClass
     * @param array $context
     * @param SearchFilterRequest $searchFilterRequest
     * @return iterable<Status>
     */
    public function retrieveAll(string $resourceClass, array $context, SearchFilterRequest $searchFilterRequest): iterable
    {
        $offset = $searchFilterRequest->getOffset();
        $numberOfItems = $searchFilterRequest->getNumberOfItems();
        $filter = (array_key_exists('status', $searchFilterRequest->getSearches()))
            ? function (Status $status) use ($searchFilterRequest) { return $status->getStatus() === $searchFilterRequest->getSearches()['status']; }
            : function () { return true; };
        return new LimitIterator(
            new CallbackFilterIterator(
                new RewindableGenerator(function () {
                    return $this->iterate();
                }),
                $filter
            ),
            $offset,
            $numberOfItems
        );
    }

    /**
     * Retrieves search filter for an api resource.
     *
     * @param ApiResourceClassMetadata $classMetadata
     * @return SearchFilter
     */
    public function getSearchFilter(ApiResourceClassMetadata $classMetadata): SearchFilter
    {
        $res = new SearchFilter();
        $res->addPrimitiveSearchFilter('status', PhpPrimitive::STRING);
        return $res;
    }
}
