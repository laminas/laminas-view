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
            'dependencies'       => $this->getDependencies(),
            'view_manager'       => [
                'encoding' => 'utf-8',
            ],
            'view_helper_config' => [
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
                Escaper::class => EscaperFactory::class,
            ],
        ];
    }
}
