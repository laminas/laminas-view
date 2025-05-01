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
            'view_helper_config' => [
                /**
                 * Encoding is passed to the Escaper which is consumed by a number of helpers
                 */
                'encoding' => 'utf-8',
                /**
                 * Maps asset names to resources for the `Asset` helper
                 */
                'asset' => [
                    'resource_map' => [],
                ],

                /**
                 * The base path is a string provided to the BasePath view helper
                 */
                'base_path' => null,
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
