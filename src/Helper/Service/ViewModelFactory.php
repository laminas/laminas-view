<?php

declare(strict_types=1);

namespace Laminas\View\Helper\Service;

use Laminas\View\Helper\Layout;
use Laminas\View\Helper\ViewModel;
use Laminas\View\HelperPluginManager;
use Psr\Container\ContainerInterface;

/**
 * @internal
 *
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 */
final readonly class ViewModelFactory
{
    public function __invoke(ContainerInterface $container): ViewModel
    {
        $helpers = $container->get(HelperPluginManager::class);

        return new ViewModel($helpers->get(Layout::class));
    }
}
