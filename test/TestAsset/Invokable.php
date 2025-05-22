<?php

declare(strict_types=1);

namespace LaminasTest\View\TestAsset;

final class Invokable
{
    /**
     * Invokable functor
     */
    public function __invoke(string $message): string
    {
        return __METHOD__ . ': ' . $message;
    }
}
