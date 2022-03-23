<?php

declare(strict_types=1);

namespace LaminasTest\View\TestAsset;

use Laminas\View\Helper\AbstractHelper as Helper;

class SharedInstance extends Helper
{
    private int $count = 0;

    /**
     * Invokable functor
     */
    public function __invoke(): int
    {
        $this->count++;

        return $this->count;
    }
}
