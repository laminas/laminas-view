<?php

declare(strict_types=1);

namespace Laminas\View\Helper\Service;

use Laminas\Escaper\EscaperInterface;
use Laminas\View\Helper\Doctype;
use Laminas\View\Helper\HtmlObject;
use Laminas\View\HelperPluginManager;
use Psr\Container\ContainerInterface;

/** @internal */
final class HtmlObjectFactory
{
    public function __invoke(ContainerInterface $container): HtmlObject
    {
        $helpers = $container->get(HelperPluginManager::class);

        return new HtmlObject(
            $container->get(EscaperInterface::class),
            $helpers->get(Doctype::class),
        );
    }
}
