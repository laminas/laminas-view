<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\Service;

use Laminas\Escaper\Escaper;
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\Helper\Service\EscaperFactoryTrait;
use Psr\Container\ContainerInterface;

final class StubEscaperFactory
{
    use EscaperFactoryTrait;

    public function __invoke(ContainerInterface $container): EscapeHtml
    {
        return new EscapeHtml($this->retrieveOrCreateEscaper($container));
    }

    public function getEscaper(ContainerInterface $container): Escaper
    {
        return $this->retrieveOrCreateEscaper($container);
    }
}
