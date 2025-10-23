<?php

declare(strict_types=1);

namespace Laminas\View\Helper\Service;

use Laminas\View\Helper\Partial;
use Laminas\View\Renderer\PhpRenderer;
use Psr\Container\ContainerInterface;

/**
 * @internal
 *
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 */
final readonly class PartialFactory
{
    public function __invoke(ContainerInterface $container): Partial
    {
        return new Partial($container->get(PhpRenderer::class));
    }
}
