<?php

declare(strict_types=1);

namespace Laminas\View\Resolver\Factory;

use Laminas\View\Helper\ViewModel;
use Laminas\View\HelperPluginManager;
use Laminas\View\Resolver\AggregateResolver;
use Laminas\View\Resolver\PrefixPathStackResolver;
use Laminas\View\Resolver\RelativeFallbackResolver;
use Laminas\View\Resolver\TemplateMapResolver;
use Laminas\View\Resolver\TemplatePathStack;
use Psr\Container\ContainerInterface;

/**
 * @internal
 *
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 */
final readonly class AggregateResolverFactory
{
    public function __invoke(ContainerInterface $container): AggregateResolver
    {
        $mapResolver     = $container->get(TemplateMapResolver::class);
        $stackResolver   = $container->get(TemplatePathStack::class);
        $prefixResolver  = $container->get(PrefixPathStackResolver::class);
        $pluginManager   = $container->get(HelperPluginManager::class);
        $viewModelHelper = $pluginManager->get(ViewModel::class);

        return new AggregateResolver([
            $mapResolver,
            $stackResolver,
            $prefixResolver,
            new RelativeFallbackResolver($mapResolver, $viewModelHelper),
            new RelativeFallbackResolver($stackResolver, $viewModelHelper),
            new RelativeFallbackResolver($prefixResolver, $viewModelHelper),
        ]);
    }
}
