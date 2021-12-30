<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\TestAsset;

class Aggregate
{
    /** @var array<string, string> */
    private $vars = [
        'foo' => 'bar',
        'bar' => 'baz',
    ];

    /** @return array<string, string> */
    public function toArray(): array
    {
        return $this->vars;
    }
}
