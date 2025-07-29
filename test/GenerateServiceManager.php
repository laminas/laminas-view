<?php

declare(strict_types=1);

namespace LaminasTest\View;

use Laminas\ServiceManager\ServiceManager;
use Laminas\View\ConfigProvider;

use function array_replace_recursive;
use function assert;
use function is_array;

/**
 * Test Utility to fetch a configured service manager with specific configuration overrides.
 *
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 */
final class GenerateServiceManager
{
    /** @param array<string, mixed> $config */
    public static function withConfig(array $config = []): ServiceManager
    {
        /** @psalm-var ServiceManagerConfiguration $config */
        $config = array_replace_recursive(
            (new ConfigProvider())->__invoke(),
            $config,
        );

        assert(isset($config['dependencies']) && is_array($config['dependencies']));
        if (! isset($config['dependencies']['services']) || ! is_array($config['dependencies']['services'])) {
            $config['dependencies']['services'] = [];
        }

        $config['dependencies']['services']['config'] = $config;

        /** @psalm-suppress MixedArgumentTypeCoercion Because of the merge with an unspecified array */

        return new ServiceManager($config['dependencies']);
    }
}
