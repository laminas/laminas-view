<?php

declare(strict_types=1);

namespace LaminasTest\View\Model\TestAsset;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

/** @implements IteratorAggregate<string, mixed> */
final class Variable implements IteratorAggregate
{
    /** @param array<string, mixed> $data */
    public function __construct(private readonly array $data = [])
    {
    }

    /** @return Traversable<string, mixed> */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }
}
