<?php

declare(strict_types=1);

namespace Laminas\View;

use Laminas\ServiceManager\PluginManagerInterface;
use Laminas\View\Helper\HelperInterface;

/** @extends PluginManagerInterface<HelperInterface|callable> */
interface HelperPluginManagerInterface extends PluginManagerInterface
{
    /**
     * Resets the internal state built up in any view helpers so that further rendering cycles are not polluted
     */
    public function resetState(): void;
}
