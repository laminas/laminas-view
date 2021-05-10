<?php

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
