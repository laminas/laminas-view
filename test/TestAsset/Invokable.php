<?php

declare(strict_types=1);

namespace LaminasTest\View\TestAsset;

use Laminas\View\Helper\AbstractHelper as Helper;

class Invokable extends Helper
{
    /**
     * Invokable functor
     */
    public function __invoke(string $message): string
    {
        return __METHOD__ . ': ' . $message;
    }
}
