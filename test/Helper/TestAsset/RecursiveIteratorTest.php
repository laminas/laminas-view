<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\Helper\TestAsset;

use Iterator;

class RecursiveIteratorTest implements Iterator
{
    public $items;

    public function __construct()
    {
        $this->items = [];
    }

    public function addItem(Iterator $iterator)
    {
        $this->items[] = $iterator;
        return $this;
    }

    public function current()
    {
        return current($this->items);
    }

    public function key()
    {
        return key($this->items);
    }

    public function next()
    {
        return next($this->items);
    }

    public function rewind()
    {
        return reset($this->items);
    }

    public function valid()
    {
        return (current($this->items) !== false);
    }
}
