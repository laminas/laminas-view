<?php

declare(strict_types=1);

namespace Laminas\View\Resolver\Factory;

use Laminas\View\ConfigProvider;
use Laminas\View\Helper\Service\Configuration;
use Laminas\View\Resolver\TemplatePathStack;
use Psr\Container\ContainerInterface;

/**
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 * @psalm-import-type ViewConfigShape from ConfigProvider
 */
final class TemplatePathStackResolverFactory
{
    public function __invoke(ContainerInterface $container): TemplatePathStack
    {
        /** @var ViewConfigShape $config */
        $config = Configuration::get($container);
        $paths  = $config['view_manager']['template_path_stack'] ?? [];

        return new TemplatePathStack([
            'default_suffix' => Configuration::defaultTemplateSuffix($container),
            'script_paths'   => $paths,
        ]);
    }
}
