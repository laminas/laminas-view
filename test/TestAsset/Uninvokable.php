<?php

declare(strict_types=1);

namespace LaminasTest\View\TestAsset;

use Laminas\View\Helper\HelperInterface;
use Stringable;

final class Uninvokable implements HelperInterface, Stringable
{
    public readonly string $value;

    public function __construct()
    {
        $this->value = 'String Output';
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
