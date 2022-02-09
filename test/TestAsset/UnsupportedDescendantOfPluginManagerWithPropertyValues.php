<?php

declare(strict_types=1);

namespace LaminasTest\View\TestAsset;

use Laminas\ServiceManager\ConfigInterface;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\View\HelperPluginManager;

/**
 * This test asset can be removed in 3.0 when the plugin manager can be made final
 *
 * @deprecated Remove this in 3.0
 *
 * @psalm-import-type FactoriesConfigurationType from ConfigInterface
 * @psalm-suppress InvalidExtendClass
 */
final class UnsupportedDescendantOfPluginManagerWithPropertyValues extends HelperPluginManager
{
    /** @var array<string, string>|array<array-key, string> */
    protected $aliases = [
        'aliasForTestHelper' => Invokable::class,
    ];

    /** @var FactoriesConfigurationType */
    protected $factories = [
        Invokable::class => InvokableFactory::class,
    ];
}
