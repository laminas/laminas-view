<?php

declare(strict_types=1);

namespace Laminas\View\Resolver\Factory;

use Laminas\View\Helper\Service\Configuration;
use Laminas\View\Resolver\PrefixPathStackResolver;
use Psr\Container\ContainerInterface;

use function is_array;

final class PrefixPathStackResolverFactory
{
    public function __invoke(ContainerInterface $container): PrefixPathStackResolver
    {
        $config = Configuration::get($container);
        /** @var mixed $paths */
        $paths = $config['view_manager']['prefix_template_path_stack'] ?? [];
        /**
         * Forcing this type to avoid runtime validation of configuration
         *
         * @var array<non-empty-string, non-empty-string> $paths
         */
        $paths = is_array($paths) ? $paths : [];

        return new PrefixPathStackResolver(
            $paths,
            Configuration::defaultTemplateSuffix($container),
        );
    }
}
