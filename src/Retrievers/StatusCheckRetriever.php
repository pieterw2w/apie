<?php

namespace W2w\Lib\Apie\Retrievers;

use Generator;
use LimitIterator;
use RewindableGenerator;
use Symfony\Component\HttpKernel\Exception\HttpException;
use UnexpectedValueException;
use W2w\Lib\Apie\ApiResources\Status;
use W2w\Lib\Apie\StatusChecks\StatusCheckInterface;
use W2w\Lib\Apie\StatusChecks\StatusCheckListInterface;

/**
 * Status check retriever retrieves instances of Status. A status check needs to implement StatusCheckInterface
 * or StatusCheckListInterface and sent in the constructor of this method.
 */
class StatusCheckRetriever implements ApiResourceRetrieverInterface
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
                    yield $check->getStatus();
                }
            }
            if (!$check) {
                throw new UnexpectedValueException(
                    'A status check should implement StatusCheckInterface or StatusCheckListInterface'
                );
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
        throw new HttpException(404, 'Status ' . $id . ' not found!');
    }

    /**
     * Return all status check results.
     *
     * @param string $resourceClass
     * @param array $context
     * @param int $pageIndex
     * @param int $numberOfItems
     * @return Status[]
     */
    public function retrieveAll(string $resourceClass, array $context, int $pageIndex, int $numberOfItems): iterable
    {
        return new LimitIterator(new RewindableGenerator(function () {
            return $this->iterate();
        }), $pageIndex, $numberOfItems);
    }
}
