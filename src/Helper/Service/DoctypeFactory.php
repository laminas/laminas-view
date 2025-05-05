<?php

declare(strict_types=1);

namespace Laminas\View\Helper\Service;

use Laminas\View\ConfigProvider;
use Laminas\View\Helper\Doctype;
use Psr\Container\ContainerInterface;

/**
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 * @psalm-import-type ViewConfigShape from ConfigProvider
 */
final class DoctypeFactory
{
    public function __invoke(ContainerInterface $container): Doctype
    {
        /** @var ViewConfigShape $config */
        $config  = Configuration::get($container);
        $doctype = $config['view_manager']['doctype'] ?? Doctype::DEFAULT_DOCTYPE;
        $doctype = $config['view_helper_config']['doctype'] ?? $doctype;

        return new Doctype($doctype);
    }
}
