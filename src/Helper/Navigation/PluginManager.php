<?php

namespace Laminas\View\Helper\Navigation;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\ConfigInterface;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\View\HelperPluginManager;

/**
 * Plugin manager implementation for navigation helpers
 *
 * Enforces that helpers retrieved are instances of
 * Navigation\HelperInterface. Additionally, it registers a number of default
 * helpers.
 */
class PluginManager extends HelperPluginManager
{
    protected $instanceOf = AbstractHelper::class;

    /**
     * Default aliases
     *
     * @var array<string, string>
     */
    protected $aliases = [
        'breadcrumbs' => Breadcrumbs::class,
        'links'       => Links::class,
        'menu'        => Menu::class,
        'sitemap'     => Sitemap::class,

        // Legacy Zend Framework aliases
        \Zend\View\Helper\Navigation\Breadcrumbs::class => Breadcrumbs::class,
        \Zend\View\Helper\Navigation\Links::class => Links::class,
        \Zend\View\Helper\Navigation\Menu::class => Menu::class,
        \Zend\View\Helper\Navigation\Sitemap::class => Sitemap::class,

        // v2 normalized FQCNs
        'zendviewhelpernavigationbreadcrumbs' => Breadcrumbs::class,
        'zendviewhelpernavigationlinks' => Links::class,
        'zendviewhelpernavigationmenu' => Menu::class,
        'zendviewhelpernavigationsitemap' => Sitemap::class,
    ];

    /**
     * Default factories
     */
    protected $factories = [
        Breadcrumbs::class => InvokableFactory::class,
        Links::class       => InvokableFactory::class,
        Menu::class        => InvokableFactory::class,
        Sitemap::class     => InvokableFactory::class,

        // v2 canonical FQCNs

        'laminasviewhelpernavigationbreadcrumbs' => InvokableFactory::class,
        'laminasviewhelpernavigationlinks'       => InvokableFactory::class,
        'laminasviewhelpernavigationmenu'        => InvokableFactory::class,
        'laminasviewhelpernavigationsitemap'     => InvokableFactory::class,
    ];

    /**
     * @param null|ConfigInterface|ContainerInterface $configOrContainerInstance
     * @param array $v3config If $configOrContainerInstance is a container, this
     *     value will be passed to the parent constructor.
     */
    public function __construct($configOrContainerInstance = null, array $v3config = [])
    {
        /** @psalm-suppress MissingClosureParamType */
        $this->initializers[] = function (ContainerInterface $container, $instance): void {
            if (! $instance instanceof AbstractHelper) {
                return;
            }

            $instance->setServiceLocator($container);
        };

        parent::__construct($configOrContainerInstance, $v3config);
    }
}
