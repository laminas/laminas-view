<?php

declare(strict_types=1);

namespace Laminas\View\Resolver\Factory;

use Laminas\View\Helper\Service\Configuration;
use Laminas\View\Resolver\TemplatePathStack;
use Psr\Container\ContainerInterface;

use function is_array;

final class TemplatePathStackResolverFactory
{
    /** @psalm-suppress MixedAssignment */
    public function __invoke(ContainerInterface $container): TemplatePathStack
    {
        $config = Configuration::get($container);

        $paths = $config['view_manager']['template_path_stack'] ?? [];
        /** @psalm-var list<non-empty-string> $paths */
        $paths = is_array($paths) ? $paths : [];

        return new TemplatePathStack([
            'default_suffix' => Configuration::defaultTemplateSuffix($container),
            'script_paths'   => $paths,
        ]);
    }
}
