<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\TestAsset;

use Stringable;

final class StringableObject implements Stringable
{
    public function __construct(public readonly string $value = self::class)
    {
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
