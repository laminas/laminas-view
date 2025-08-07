<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\TestAsset;

final class ToArray
{
    /** @param array<array-key, mixed> $array */
    public function __construct(public array $array = [])
    {
    }

    /**
     * @deprecated This should be removed in 4.0
     *
     * @return array<array-key, mixed>
     * @psalm-api Used to check duck-typing in Abstract escape helper operates as expected
     */
    public function toArray(): array
    {
        return $this->array;
    }
}
