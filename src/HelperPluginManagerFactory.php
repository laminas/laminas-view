<?php

declare(strict_types=1);

namespace Laminas\View;

use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Helper\Service\Configuration;
use Psr\Container\ContainerInterface;

use function is_array;

/**
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 */
final class HelperPluginManagerFactory
{
    public function __invoke(ContainerInterface $container): HelperPluginManager
    {
        $applicationConfig = Configuration::get($container);
        /** @var mixed $helperConfig */
        $helperConfig = $applicationConfig['view_helpers'] ?? [];

        /**
         * Forcing the type for this variable because runtime validation is unnecessary
         *
         * @psalm-var ServiceManagerConfiguration $helperConfig
         */
        $helperConfig = is_array($helperConfig) ? $helperConfig : [];

        return new HelperPluginManager($container, $helperConfig);
    }
}
