<?php

declare(strict_types=1);

namespace Laminas\View\Helper\Service;

use Laminas\View\Helper\RenderChildModel;
use Laminas\View\Helper\ViewModel;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;
use Psr\Container\ContainerInterface;

final readonly class RenderChildModelFactory
{
    public function __invoke(ContainerInterface $container): RenderChildModel
    {
        $helpers = $container->get(HelperPluginManager::class);

        return new RenderChildModel(
            $helpers->get(ViewModel::class),
            $container->get(PhpRenderer::class),
        );
    }
}
