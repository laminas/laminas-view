<?php

declare(strict_types=1);

namespace LaminasTest\View\TestAsset;

final class SharedInstance
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
