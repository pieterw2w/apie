<?php

namespace W2w\Lib\Apie\Retriever;

use App\Services\StatusCheck\StatusCheckInterface;
use App\Services\StatusCheck\StatusCheckListInterface;
use Generator;
use LimitIterator;
use RewindableGenerator;
use Symfony\Component\HttpKernel\Exception\HttpException;
use UnexpectedValueException;

class StatusCheckRetriever implements ApiResourceRetrieverInterface
{
    private $statusChecks;

    public function __construct(iterable $statusChecks)
    {
        $this->statusChecks = $statusChecks;
    }

    public function iterate(): Generator
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

    public function retrieve(string $resourceClass, $id, array $context)
    {
        foreach ($this->iterate() as $statusCheck) {
            if ($statusCheck->getId() === $id) {
                return $statusCheck;
            }
        }
        throw new HttpException(404, 'Status ' . $id . ' not found!');
    }

    public function retrieveAll(string $resourceClass, array $context, int $pageIndex, int $numberOfItems): iterable
    {
        return new LimitIterator(new RewindableGenerator(function () {
            return $this->iterate();
        }), $pageIndex, $numberOfItems);
    }
}
