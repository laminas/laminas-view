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
 */
class IteratorTest implements Iterator
{
    /** @var array<array-key, T> */
    public $items;

    /** @param array<array-key, T> $array */
    public function __construct(array $array)
    {
        $this->items = $array;
    }

    /**
     * @return T|false
     */
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

    /**
     * @return array<array-key, T>
     */
    public function toArray(): array
    {
        return $this->items;
    }
}
