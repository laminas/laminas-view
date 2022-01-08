<?php

declare(strict_types=1);

namespace LaminasTest\View\TestAsset;

use Laminas\View\Helper\AbstractHelper as Helper;

class SharedInstance extends Helper
{
    /** @var int */
    private $count = 0;

    /**
     * Invokable functor
     */
    public function __invoke(): int
    {
        $this->count++;

        return $this->count;
    }
}
