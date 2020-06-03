<?php


namespace W2w\Test\Apie\Mocks\ValueObjects;


use Tightenco\Collect\Support\Collection;
use UnexpectedValueException;
use W2w\Test\Apie\Mocks\ApiResources\SumExample;

class ObjectWithCollection
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var Collection|null
     */
    private $optionalCollection;

    public function __construct($collection = [])
    {
        $this->setCollection($collection);
    }

    /**
     * @return Collection
     */
    public function getCollection(): Collection
    {
        return $this->collection;
    }

    /**
     * @param Collection $collection
     */
    public function setCollection(Collection $collection): void
    {
        foreach ($collection as $key => $value) {
            if (!($value instanceof SumExample)) {
                throw new UnexpectedValueException('I expect to only have instances of SumExample! Offset ' . $key . ' is not.');
            }
        }
        $this->collection = $collection;
    }

    /**
     * Sum of all
     *
     * @return int
     */
    public function getAddition(): int
    {
        $sum = 0;
        foreach ($this->collection as $value) {
            /** @var SumExample $value */
            $sum += $value->getAddition();
        }
        if ($this->optionalCollection) {
            foreach ($this->optionalCollection as $value) {
                /** @var SumExample $value */
                $sum += $value->getAddition();
            }
        }
        return $sum;
    }

    /**
     * @return Collection|null
     */
    public function getOptionalCollection(): ?Collection
    {
        return $this->optionalCollection;
    }

    /**
     * @param Collection|null $optionalCollection
     */
    public function setOptionalCollection(?Collection $optionalCollection): void
    {
        foreach ($optionalCollection as $key => $value) {
            if (!($value instanceof SumExample)) {
                throw new UnexpectedValueException('I expect to only have instances of SumExample! Offset ' . $key . ' is not.');
            }
        }
        $this->optionalCollection = $optionalCollection;
    }
}
