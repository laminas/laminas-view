<?php

declare(strict_types=1);

namespace Laminas\View\Helper\Service;

use Laminas\View\Helper\Placeholder;
use Laminas\View\Helper\RenderToPlaceholder;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;
use Psr\Container\ContainerInterface;

final class RenderToPlaceholderFactory
{
    public function __invoke(ContainerInterface $container): RenderToPlaceholder
    {
        $helpers = $container->get(HelperPluginManager::class);

        return new RenderToPlaceholder(
            $container->get(PhpRenderer::class),
            $helpers->get(Placeholder::class),
        );
    }
}
