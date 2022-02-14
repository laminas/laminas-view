<?php

declare(strict_types=1);

namespace Laminas\View\Helper\Service;

use Laminas\View\Helper\EscapeCss;
use Psr\Container\ContainerInterface;

final class EscapeCssFactory
{
    use EscaperFactoryTrait;

    public function __invoke(ContainerInterface $container): EscapeCss
    {
        return new EscapeCss($this->retrieveOrCreateEscaper($container));
    }
}
