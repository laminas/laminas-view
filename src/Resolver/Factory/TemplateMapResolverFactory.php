<?php

declare(strict_types=1);

namespace Laminas\View\Resolver\Factory;

use Laminas\View\ConfigProvider;
use Laminas\View\Factory\Configuration;
use Laminas\View\Resolver\TemplateMapResolver;
use Psr\Container\ContainerInterface;

/**
 * @internal
 *
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 * @psalm-import-type ViewConfigShape from ConfigProvider
 */
final readonly class TemplateMapResolverFactory
{
    public function __invoke(ContainerInterface $container): TemplateMapResolver
    {
        /** @var ViewConfigShape $config */
        $config = Configuration::get($container);

        /**
         * In laminas MVC applications, we find the template map under `view_manager.template_map`
         */
        $mvcMap = $config['view_manager']['template_map'] ?? [];

        /**
         * In Mezzio applications, the template map can be found under `templates.map`
         */
        $mezzioMap = $config['templates']['map'] ?? [];

        $mapResolver = new TemplateMapResolver();
        $mapResolver->add($mvcMap);
        $mapResolver->add($mezzioMap);

        return $mapResolver;
    }
}
