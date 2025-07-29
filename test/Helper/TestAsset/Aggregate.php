<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\TestAsset;

final class Aggregate
{
    /** @var array<string, string> */
    private array $vars = [
        'foo' => 'bar',
        'bar' => 'baz',
    ];

    /**
     * @deprecated Remove in 4.0
     *
     * @return array<string, string>
     * @psalm-api Used to test deprecated duck typing
     */
    public function toArray(): array
    {
        return $this->vars;
    }
}
