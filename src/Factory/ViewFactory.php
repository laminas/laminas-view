<?php

declare(strict_types=1);

namespace Laminas\View\Factory;

use Laminas\View\HelperPluginManagerInterface;
use Laminas\View\Renderer\RendererInterface;
use Laminas\View\View;
use Psr\Container\ContainerInterface;

final readonly class ViewFactory
{
    public function __invoke(ContainerInterface $container): View
    {
        return new View(
            $container->get(RendererInterface::class),
            $container->get(HelperPluginManagerInterface::class),
            Configuration::defaultLayout($container),
            Configuration::defaultCaptureTo($container),
        );
    }
}
