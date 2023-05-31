<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\TestAsset;

use Iterator;
use ReturnTypeWillChange; // phpcs:ignore

use function current;
use function key;
use function next;
use function reset;

/**
 * @template T
 * @implements Iterator<array-key, T>
 */
class PartialLoopIteratorWithToArray implements Iterator
{
    /** @var array<array-key, T> */
    public array $items;

    /** @param array<array-key, T> $array */
    public function __construct(array $array)
    {
        $this->items = $array;
    }

    /** @return array<array-key, T> */
    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * @return T
     */
    #[ReturnTypeWillChange]
    public function current()
    {
        return current($this->items);
    }

    public function key(): int|string
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
