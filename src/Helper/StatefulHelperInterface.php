<?php

declare(strict_types=1);

namespace Laminas\View\Helper;

/**
 * This interface defines a view helper that maintains state and provides a way to reset that state
 */
interface StatefulHelperInterface
{
    public function resetState(): void;
}
