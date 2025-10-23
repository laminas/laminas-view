<?php

declare(strict_types=1);

namespace Laminas\View\Factory;

use Laminas\Escaper\Escaper;
use Psr\Container\ContainerInterface;

/**
 * @internal
 *
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 */
final readonly class EscaperFactory
{
    public function __invoke(ContainerInterface $container): Escaper
    {
        return new Escaper(
            Configuration::viewEncoding($container),
        );
    }
}
