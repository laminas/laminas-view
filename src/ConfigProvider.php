<?php

declare(strict_types=1);

namespace Laminas\View;

use Laminas\ServiceManager\ConfigInterface;

/**
 * @see ConfigInterface
 *
 * @psalm-import-type ServiceManagerConfigurationType from ConfigInterface
 */
final class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies'       => $this->getDependencyConfig(),
            'view_helpers'       => $this->getViewHelperDependencyConfiguration(),
            'view_helper_config' => $this->getViewHelperConfiguration(),
        ];
    }

    /** @return ServiceManagerConfigurationType */
    public function getDependencyConfig(): array
    {
        return [
            'factories' => [],
            'aliases'   => [],
        ];
    }

    /** @return ServiceManagerConfigurationType */
    public function getViewHelperDependencyConfiguration(): array
    {
        return [
            'factories' => [],
            'aliases'   => [],
        ];
    }

    /** @return array<string, mixed> */
    public function getViewHelperConfiguration(): array
    {
        return [
            'asset' => [
                /**
                 * The asset helper is configured with a map asset names as keys a relative file paths as values
                 *
                 * @see Helper\Asset
                 * @see Helper\Service\AssetFactory
                 */
                'resource_map' => [],
            ],

            /**
             * @see Helper\Doctype
             * @see Helper\Service\DoctypeFactory
             */
            'doctype' => null,
        ];
    }
}
