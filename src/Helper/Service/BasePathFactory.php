<?php

declare(strict_types=1);

namespace Laminas\View\Helper\Service;

use Laminas\View\ConfigProvider;
use Laminas\View\Factory\Configuration;
use Laminas\View\Helper\BasePath;
use Psr\Container\ContainerInterface;

use function assert;
use function is_string;

/**
 * @psalm-import-type ViewConfigShape from ConfigProvider
 */
final class BasePathFactory
{
    public function __invoke(ContainerInterface $container): BasePath
    {
        /** @var ViewConfigShape $config */
        $config = Configuration::get($container);

        // The expected location in config for the base path in an MVC application
        // is config.view_manager.base_path
        // @link https://docs.laminas.dev/laminas-mvc/services/#viewmanager
        // More recently, we look in `view_helper_config.base_path`

        $basePath = $config['view_manager']['base_path'] ?? null;
        $basePath = $config['view_helper_config']['base_path'] ?? $basePath;

        assert(is_string($basePath) || $basePath === null);

        return new BasePath($basePath);
    }
}
