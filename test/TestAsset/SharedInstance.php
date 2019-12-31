<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\TestAsset;

use Laminas\View\Helper\AbstractHelper as Helper;

class SharedInstance extends Helper
{
    protected $count = 0;

    /**
     * Invokable functor
     *
     * @return int
     */
    public function __invoke()
    {
        $this->count++;

        return $this->count;
    }
}
