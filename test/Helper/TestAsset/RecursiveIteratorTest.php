<?php

namespace LaminasTest\View\Helper\TestAsset;

use Iterator;
use ReturnTypeWillChange; // phpcs:ignore

use function current;
use function key;
use function next;
use function reset;

class RecursiveIteratorTest implements Iterator
{
    public $items;

    public function __construct()
    {
        $this->items = [];
    }

    /**
     * @return static
     */
    public function addItem(Iterator $iterator): self
    {
        $this->items[] = $iterator;
        return $this;
    }

    #[ReturnTypeWillChange]
    public function current()
    {
        return current($this->items);
    }

    #[ReturnTypeWillChange]
    public function key()
    {
        return key($this->items);
    }

    #[ReturnTypeWillChange]
    public function next()
    {
        return next($this->items);
    }

    #[ReturnTypeWillChange]
    public function rewind()
    {
        return reset($this->items);
    }

    #[ReturnTypeWillChange]
    public function valid()
    {
        return (current($this->items) !== false);
    }
}
