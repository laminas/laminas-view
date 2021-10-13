<?php

namespace LaminasTest\View\Model\TestAsset;

use Iterator;
use ReturnTypeWillChange;

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

    #[ReturnTypeWillChange]
    /**
     * @return void
     */
    public function valid()
    {
    }
}
