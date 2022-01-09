<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\TestAsset;

use Iterator;
use ReturnTypeWillChange; // phpcs:ignore

use function current;
use function key;
use function next;
use function reset;

class RecursiveIteratorTest implements Iterator
{
    /** @var array<array-key, Iterator> */
    public $items;

    public function __construct()
    {
        $this->items = [];
    }

    /**
     * @return $this
     */
    public function addItem(Iterator $iterator): self
    {
        $this->items[] = $iterator;
        return $this;
    }

    /** @return Iterator|false */
    #[ReturnTypeWillChange]
    public function current()
    {
        return current($this->items);
    }

    /**
     * @return array-key|null
     */
    #[ReturnTypeWillChange]
    public function key()
    {
        return key($this->items);
    }

    #[ReturnTypeWillChange]
    public function next(): void
    {
        next($this->items);
    }

    #[ReturnTypeWillChange]
    public function rewind(): void
    {
        reset($this->items);
    }

    #[ReturnTypeWillChange]
    public function valid(): bool
    {
        return current($this->items) !== false;
    }
}
