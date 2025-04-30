<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\TestAsset;

final class ToArray
{
    /** @param array<array-key, mixed> $array */
    public function __construct(public array $array = [])
    {
    }

    /** @return array<array-key, mixed> */
    public function toArray(): array
    {
        return $this->array;
    }
}
