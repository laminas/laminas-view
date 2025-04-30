<?php

declare(strict_types=1);

namespace Laminas\View\Helper;

final class EscapeHtmlAttr extends Escaper\AbstractHelper
{
    protected function escape(string $value): string
    {
        return $this->escaper->escapeHtmlAttr($value);
    }
}
