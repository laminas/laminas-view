<?php

declare(strict_types=1);

namespace Laminas\View\Helper\Service;

use Laminas\View\Helper\Partial;
use Laminas\View\Helper\PartialLoop;
use Laminas\View\HelperPluginManager;
use Psr\Container\ContainerInterface;

/**
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 */
final class PartialLoopFactory
{
    public function __invoke(ContainerInterface $container): PartialLoop
    {
        $helpers = $container->get(HelperPluginManager::class);

        return new PartialLoop($helpers->get(Partial::class));
    }
}
