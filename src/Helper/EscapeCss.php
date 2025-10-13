<?php

declare(strict_types=1);

namespace Laminas\View\Helper;

final readonly class EscapeCss extends Escaper\AbstractHelper
{
    protected function escape(string $value): string
    {
        return $this->escaper->escapeCss($value);
    }
}
