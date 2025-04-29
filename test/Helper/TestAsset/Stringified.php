<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\TestAsset;

final class Stringified
{
    public function __toString(): string
    {
        return static::class;
    }
}
