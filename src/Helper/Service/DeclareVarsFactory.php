<?php

declare(strict_types=1);

namespace Laminas\View\Helper\Service;

use Laminas\View\Helper\DeclareVars;
use Laminas\View\Renderer\PhpRenderer;
use Psr\Container\ContainerInterface;

/**
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 */
final class DeclareVarsFactory
{
    public function __invoke(ContainerInterface $container): DeclareVars
    {
        return new DeclareVars($container->get(PhpRenderer::class));
    }
}
