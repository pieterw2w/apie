<?php


namespace W2w\Lib\Apie\Plugins\StatusCheck\Pagers;

use Iterator;
use LimitIterator;
use Pagerfanta\Adapter\AdapterInterface;

class StatusCheckPager implements AdapterInterface
{
    /**
     * @var Iterator
     */
    private $iterator;

    public function __construct(Iterator $iterator)
    {
        $this->iterator = $iterator;
    }

    /**
     * @return int
     */
    public function getNbResults()
    {
        $count = 0;
        for ($this->iterator->rewind(); $this->iterator->valid(); $this->iterator->next()) {
            $count++;
        }
        return $count;
    }

    public function getSlice($offset, $length)
    {
        return iterator_to_array(new LimitIterator($this->iterator, $offset, $length));
    }
}
