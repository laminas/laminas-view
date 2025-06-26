<?php

declare(strict_types=1);

namespace Laminas\View\Renderer;

use Laminas\View\HelperPluginManagerInterface;
use Laminas\View\Resolver\ResolverInterface;
use Psr\Container\ContainerInterface;

use function assert;
use function is_array;
use function is_bool;

final class PhpRendererFactory
{
    public function __invoke(ContainerInterface $container): PhpRenderer
    {
        $config = $this->config($container);
        $strict = $config['strict_variables'] ?? true;
        assert(is_bool($strict));

        return new PhpRenderer(
            $container->get(HelperPluginManagerInterface::class),
            $container->get(ResolverInterface::class),
            $strict,
        );
    }

    /** @return array<array-key, mixed> */
    private function config(ContainerInterface $container): array
    {
        /** @var mixed $config */
        $config = $container->has('config')
            ? $container->get('config')
            : [];

        $config = is_array($config) ? $config : [];
        /** @psalm-var mixed $set */
        $set = $config['view_manager'] ?? [];

        return is_array($set) ? $set : [];
    }
}
