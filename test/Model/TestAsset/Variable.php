<?php

declare(strict_types=1);

namespace LaminasTest\View\Model\TestAsset;

use Iterator;
use ReturnTypeWillChange; // phpcs:ignore

class Variable implements Iterator
{
    #[ReturnTypeWillChange]
    /**
     * @return void
     */
    public function current()
    {
    }

    #[ReturnTypeWillChange]
    /**
     * @return void
     */
    public function key()
    {
    }

    #[ReturnTypeWillChange]
    public function next()
    {
    }

    #[ReturnTypeWillChange]
    public function rewind()
    {
    }

    public function valid(): bool
    {
        return false;
    }
}
