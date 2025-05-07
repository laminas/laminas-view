<?php

declare(strict_types=1);

namespace Laminas\View\Helper\Service;

use Laminas\Escaper\EscaperInterface;
use Laminas\View\Helper\Doctype;
use Laminas\View\Helper\HeadLink;
use Laminas\View\HelperPluginManager;
use Psr\Container\ContainerInterface;

/**
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 */
final class HeadLinkFactory
{
    public function __invoke(ContainerInterface $container): HeadLink
    {
        $helpers = $container->get(HelperPluginManager::class);

        return new HeadLink(
            $container->get(EscaperInterface::class),
            $helpers->get(Doctype::class),
        );
    }
}
