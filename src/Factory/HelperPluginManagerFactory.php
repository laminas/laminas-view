<?php

declare(strict_types=1);

namespace Laminas\View\Factory;

use Laminas\View\ConfigProvider;
use Laminas\View\HelperPluginManager;
use Psr\Container\ContainerInterface;

/**
 * @psalm-import-type ViewConfigShape from ConfigProvider
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 */
final class HelperPluginManagerFactory
{
    public function __invoke(ContainerInterface $container): HelperPluginManager
    {
        /** @var ViewConfigShape $applicationConfig */
        $applicationConfig = Configuration::get($container);
        $helperConfig      = $applicationConfig['view_helpers'] ?? [];

        return new HelperPluginManager($container, $helperConfig);
    }
}
