<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\TestAsset;

use Iterator;

use function current;
use function key;
use function next;
use function reset;

/**
 * @template T
 * @implements Iterator<array-key, T>
 */
final class PartialLoopIterator implements Iterator
{
    /** @var array<array-key, T> */
    public array $items;

    /** @param array<array-key, T> $array */
    public function __construct(array $array)
    {
        $this->items = $array;
    }

    /**
     * @return T|null
     */
    public function current(): mixed
    {
        $item = current($this->items);

        return $item === false ? null : $item;
    }

    public function key(): int|string|null
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

    /**
     * @deprecated To remove in 4.0
     *
     * @return array<array-key, T>
     * @psalm-api Used via duck-typing
     */
    public function toArray(): array
    {
        return $this->items;
    }
}
