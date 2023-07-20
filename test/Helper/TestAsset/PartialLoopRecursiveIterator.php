<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\TestAsset;

use Iterator;

use function current;
use function key;
use function next;
use function reset;

/** @implements Iterator<array-key, Iterator> */
class PartialLoopRecursiveIterator implements Iterator
{
    /** @var array<array-key, Iterator> */
    public array $items;

    public function __construct()
    {
        $this->items = [];
    }

    public function addItem(Iterator $iterator): void
    {
        $this->items[] = $iterator;
    }

    public function current(): Iterator
    {
        return current($this->items);
    }

    public function key(): int|string
    {
        return key($this->items);
    }

    public function next(): void
    {
        next($this->items);
    }

    public function rewind(): void
    {
        reset($this->items);
    }

    public function valid(): bool
    {
        return current($this->items) !== false;
    }
}
