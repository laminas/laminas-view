<?php

declare(strict_types=1);

namespace Laminas\View\Helper\Navigation;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\ConfigInterface;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\HelperPluginManager;

/**
 * Plugin manager implementation for navigation helpers
 *
 * Enforces that helpers retrieved are instances of
 * Navigation\HelperInterface. Additionally, it registers a number of default
 * helpers.
 *
 * @final
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 * @psalm-import-type FactoriesConfigurationType from ConfigInterface
 * @psalm-suppress InvalidExtendClass
 */
class PluginManager extends HelperPluginManager
{
    /** {@inheritDoc} */
    protected $instanceOf = AbstractHelper::class;

    /**
     * Default aliases
     *
     * @var array<string, string>|array<array-key, string>
     */
    protected $aliases = [
        'breadcrumbs' => Breadcrumbs::class,
        'links'       => Links::class,
        'menu'        => Menu::class,
        'sitemap'     => Sitemap::class,

        // Legacy Zend Framework aliases
        \Zend\View\Helper\Navigation\Breadcrumbs::class => Breadcrumbs::class,
        \Zend\View\Helper\Navigation\Links::class       => Links::class, // phpcs:ignore
        \Zend\View\Helper\Navigation\Menu::class        => Menu::class,
        \Zend\View\Helper\Navigation\Sitemap::class     => Sitemap::class,

        // v2 normalized FQCNs
        'zendviewhelpernavigationbreadcrumbs' => Breadcrumbs::class,
        'zendviewhelpernavigationlinks'       => Links::class,
        'zendviewhelpernavigationmenu'        => Menu::class,
        'zendviewhelpernavigationsitemap'     => Sitemap::class,

        // v2 canonical FQCNs
        'laminasviewhelpernavigationbreadcrumbs' => Breadcrumbs::class,
        'laminasviewhelpernavigationlinks'       => Links::class,
        'laminasviewhelpernavigationmenu'        => Menu::class,
        'laminasviewhelpernavigationsitemap'     => Sitemap::class,
    ];

    /**
     * Default factories
     *
     * @var FactoriesConfigurationType
     */
    protected $factories = [
        Breadcrumbs::class => InvokableFactory::class,
        Links::class       => InvokableFactory::class,
        Menu::class        => InvokableFactory::class,
        Sitemap::class     => InvokableFactory::class,
    ];

    /**
     * @param null|ConfigInterface|\Psr\Container\ContainerInterface $configOrContainerInstance
     * @param array                                                  $v3config
     * @psalm-param ServiceManagerConfiguration                      $v3config
     * @psalm-suppress MethodSignatureMismatch
     */
    public function __construct($configOrContainerInstance = null, array $v3config = [])
    {
        /** @psalm-suppress UnusedClosureParam, MissingClosureParamType */
        $this->initializers[] = function (ContainerInterface $container, $instance): void {
            if (! $instance instanceof AbstractHelper) {
                return;
            }

            $instance->setServiceLocator($this->creationContext);
        };

        parent::__construct($configOrContainerInstance, $v3config);
    }
}
