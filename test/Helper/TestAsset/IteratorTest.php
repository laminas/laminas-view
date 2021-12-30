<?php

namespace LaminasTest\View\Helper\TestAsset;

use Iterator;
use ReturnTypeWillChange; // phpcs:ignore

use function current;
use function key;
use function next;
use function reset;

class IteratorTest implements Iterator
{
    public $items;

    public function __construct(array $array)
    {
        $this->items = $array;
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

    public function toArray(): array
    {
        return $this->items;
    }
}
