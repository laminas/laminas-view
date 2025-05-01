<?php

declare(strict_types=1);

namespace Laminas\View\Resolver\Factory;

use Laminas\View\Helper\Service\Configuration;
use Laminas\View\Resolver\TemplateMapResolver;
use Psr\Container\ContainerInterface;

use function is_iterable;

final class TemplateMapResolverFactory
{
    public function __invoke(ContainerInterface $container): TemplateMapResolver
    {
        $config = Configuration::get($container);

        /**
         * In laminas MVC applications, we find the template map under `view_manager.template_map`
         *
         * @psalm-var mixed $mvcMap
         */
        $mvcMap = $config['view_manager']['template_map'] ?? [];

        /**
         * In Mezzio applications, the template map can be found under `templates.map`
         *
         * @psalm-var mixed $mezzioMap
         */
        $mezzioMap = $config['templates']['map'] ?? [];

        $mapResolver = new TemplateMapResolver();

        /**
         * Iterable types are forced here because the resolver will crash for invalid maps
         */

        if (is_iterable($mvcMap)) {
            /** @psalm-var iterable<string, string> $mvcMap */
            $mapResolver->add($mvcMap);
        }

        if (is_iterable($mezzioMap)) {
            /** @psalm-var iterable<string, string> $mezzioMap */
            $mapResolver->add($mezzioMap);
        }

        return $mapResolver;
    }
}
