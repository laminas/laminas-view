<?php

declare(strict_types=1);

namespace Laminas\View;

use Laminas\Escaper\Escaper;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Helper\Service\EscaperFactory;

/** @psalm-import-type ServiceManagerConfiguration from ServiceManager */
final class ConfigProvider
{
    /** @return array{dependencies: ServiceManagerConfiguration, ...} */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            /**
             * The top-level configuration key for defining custom view helpers.
             *
             * This option should use the `ServiceManagerConfiguration` array format
             */
            'view_helpers'       => [],
            'view_manager'       => [
                /**
                 * Encoding passed to the Escaper and possibly used in other view-related configuration
                 */
                'encoding' => 'utf-8',

                /**
                 * The base path is provided to the BasePath view helper. This is the historic location for this
                 * configuration item defined in MVC apps.
                 */
                'base_path' => null,
            ],
            'view_helper_config' => [
                /**
                 * Maps asset names to resources for the `Asset` helper
                 */
                'asset' => [
                    'resource_map' => [],
                ],
            ],
        ];
    }

    /** @return ServiceManagerConfiguration */
    public function getDependencies(): array
    {
        return [
            'factories' => [
                HelperPluginManager::class => HelperPluginManagerFactory::class,
                Escaper::class             => EscaperFactory::class,
            ],
        ];
    }
}
