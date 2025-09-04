<?php

declare(strict_types=1);

namespace LaminasTest\View\Model\TestAsset;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

/** @implements IteratorAggregate<non-empty-string, mixed> */
final class Variable implements IteratorAggregate
{
    /** @param array<non-empty-string, mixed> $data */
    public function __construct(private readonly array $data = [])
    {
    }

    /** @return Traversable<non-empty-string, mixed> */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }
}
