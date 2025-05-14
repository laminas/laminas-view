<?php

declare(strict_types=1);

namespace Laminas\View\Helper\Service;

use Laminas\Escaper\EscaperInterface;
use Laminas\View\Helper\Doctype;
use Laminas\View\Helper\HtmlTag;
use Laminas\View\HelperPluginManager;
use Psr\Container\ContainerInterface;

/** @internal */
final class HtmlTagFactory
{
    public function __invoke(ContainerInterface $container): HtmlTag
    {
        $helpers = $container->get(HelperPluginManager::class);

        return new HtmlTag(
            $container->get(EscaperInterface::class),
            $helpers->get(Doctype::class),
        );
    }
}
