<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

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
