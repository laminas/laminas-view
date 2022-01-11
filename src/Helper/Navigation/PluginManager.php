<?php

declare(strict_types=1);

namespace Laminas\View\Helper\Navigation;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\ConfigInterface;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\HelperPluginManager;

/**
 * Plugin manager implementation for navigation helpers
 *
 * Enforces that helpers retrieved are instances of
 * Navigation\HelperInterface. Additionally, it registers a number of default
 * helpers.
 *
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 * @psalm-import-type FactoriesConfigurationType from ConfigInterface
 */
class PluginManager extends HelperPluginManager
{
    /** {@inheritDoc} */
    protected $instanceOf = AbstractHelper::class;

    /** @var array<string, string>|array<array-key, string> */
    protected $aliases;

    /** @var FactoriesConfigurationType */
    protected $factories;

    /**
     * @param ContainerInterface $configOrContainerInstance
     * @param array $v3config
     * @psalm-param ServiceManagerConfiguration $v3config
     */
    public function __construct($configOrContainerInstance = null, array $v3config = [])
    {
        parent::__construct($configOrContainerInstance, $v3config);

        /** @psalm-suppress UnusedClosureParam, MissingClosureParamType */
        $this->initializers[] = function (ContainerInterface $container, $instance): void {
            if (! $instance instanceof AbstractHelper) {
                return;
            }

            $instance->setServiceLocator($this->creationContext);
        };

        $this->aliases   = ConfigProvider::defaultViewHelperAliases();
        $this->factories = ConfigProvider::defaultViewHelperFactories();
    }
}
