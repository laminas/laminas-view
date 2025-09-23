<?php

declare(strict_types=1);

namespace Laminas\View\Renderer;

use Laminas\View\Factory\Configuration;
use Laminas\View\HelperPluginManagerInterface;
use Laminas\View\Resolver\ResolverInterface;
use Psr\Container\ContainerInterface;

/**
 * @psalm-internal Laminas
 * @psalm-internal LaminasTest
 */
final class PhpRendererFactory
{
    public function __invoke(ContainerInterface $container): PhpRenderer
    {
        return new PhpRenderer(
            $container->get(HelperPluginManagerInterface::class),
            $container->get(ResolverInterface::class),
            Configuration::strictVariables($container),
        );
    }
}
