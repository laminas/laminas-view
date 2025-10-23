<?php

declare(strict_types=1);

namespace Laminas\View\Resolver\Factory;

use Laminas\View\ConfigProvider;
use Laminas\View\Factory\Configuration;
use Laminas\View\Resolver\PrefixPathStackResolver;
use Psr\Container\ContainerInterface;

/**
 * @internal
 *
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 * @psalm-import-type ViewConfigShape from ConfigProvider
 */
final readonly class PrefixPathStackResolverFactory
{
    public function __invoke(ContainerInterface $container): PrefixPathStackResolver
    {
        /** @var ViewConfigShape $config */
        $config = Configuration::get($container);
        $paths  = $config['view_manager']['prefix_template_path_stack'] ?? [];

        return new PrefixPathStackResolver(
            $paths,
            Configuration::defaultTemplateSuffix($container),
        );
    }
}
