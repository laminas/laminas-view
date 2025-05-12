<?php

declare(strict_types=1);

namespace Laminas\View\Helper\Service;

use Laminas\Escaper\EscaperInterface;
use Laminas\View\Helper\Doctype;
use Laminas\View\Helper\HeadMeta;
use Laminas\View\HelperPluginManager;
use Psr\Container\ContainerInterface;

/** @internal */
final class HeadMetaFactory
{
    public function __invoke(ContainerInterface $container): HeadMeta
    {
        $helpers = $container->get(HelperPluginManager::class);

        return new HeadMeta(
            $helpers->get(Doctype::class),
            $container->get(EscaperInterface::class),
        );
    }
}
