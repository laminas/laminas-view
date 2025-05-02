<?php

declare(strict_types=1);

namespace Laminas\View\Renderer;

use Laminas\View\HelperPluginManager;
use Psr\Container\ContainerInterface;

final class PhpRendererFactory
{
    public function __invoke(ContainerInterface $container): PhpRenderer
    {
        $renderer = new PhpRenderer();
        $renderer->setHelperPluginManager($container->get(HelperPluginManager::class));

        return $renderer;
    }
}
