<?php

declare(strict_types=1);

namespace Laminas\View\Helper\Service;

use Laminas\Escaper\Escaper;
use Psr\Container\ContainerInterface;

final class EscaperFactory
{
    public function __invoke(ContainerInterface $container): Escaper
    {
        return new Escaper(
            Configuration::viewEncoding($container),
        );
    }
}
