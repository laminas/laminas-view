<?php

declare(strict_types=1);

namespace Laminas\View\Helper\Service;

use Laminas\View\Helper\Layout;
use Laminas\View\Helper\ViewModel;
use Laminas\View\HelperPluginManager;
use Psr\Container\ContainerInterface;

/**
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 */
final readonly class LayoutFactory
{
    public function __invoke(ContainerInterface $container): Layout
    {
        $helpers = $container->get(HelperPluginManager::class);

        return new Layout($helpers->get(ViewModel::class));
    }
}
