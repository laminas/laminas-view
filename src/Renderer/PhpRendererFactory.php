<?php

declare(strict_types=1);

namespace Laminas\View\Renderer;

use Laminas\View\HelperPluginManager;
use Laminas\View\Resolver\ResolverInterface;
use Psr\Container\ContainerInterface;

final class PhpRendererFactory
{
    public function __invoke(ContainerInterface $container): PhpRenderer
    {
        return new PhpRenderer(
            $container->get(HelperPluginManager::class),
            $container->get(ResolverInterface::class),
        );
    }
}
