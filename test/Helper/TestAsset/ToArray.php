<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\View\Helper\TestAsset;

class ToArray
{
    public $array = array();

    public function toArray()
    {
        return $this->array;
    }
}
