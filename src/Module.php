<?php

declare(strict_types=1);

namespace Laminas\View;

final class Module
{
    /**
     * Return laminas-view configuration for a laminas-mvc application.
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        $provider = new ConfigProvider();
        return [
            'service_manager'    => $provider->getDependencyConfig(),
            'view_helpers'       => $provider->getViewHelperDependencyConfiguration(),
            'view_helper_config' => $provider->getViewHelperConfiguration(),
            'view_manager'       => [
                'template_map'               => [],
                'template_path_stack'        => [],
                'prefix_template_path_stack' => [],
            ],
        ];
    }
}
