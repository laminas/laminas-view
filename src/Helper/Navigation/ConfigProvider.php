<?php

declare(strict_types=1);

namespace Laminas\View\Helper\Navigation;

use Laminas\ServiceManager\ConfigInterface;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\ServiceManager;

/**
 * @psalm-import-type FactoriesConfigurationType from ConfigInterface
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 * @psalm-import-type ViewHelperConfigurationType from \Laminas\View\ConfigProvider
 * @psalm-suppress DeprecatedClass
 */
final class ConfigProvider
{
    /**
     * @return array<string, mixed>
     * @psalm-return ViewHelperConfigurationType
     */
    public function __invoke(): array
    {
        return [
            'view_helpers'       => [
                'factories' => self::defaultViewHelperFactories(),
                'aliases'   => self::defaultViewHelperAliases(),
            ],
            'view_helper_config' => [],
        ];
    }

    /** @return array<string,string>|array<array-key, string> */
    public static function defaultViewHelperAliases(): array
    {
        return [
            'breadcrumbs' => Breadcrumbs::class,
            'links'       => Links::class,
            'menu'        => Menu::class,
            'sitemap'     => Sitemap::class,

            // Legacy Zend Framework aliases
            // @codingStandardsIgnoreStart
            'Zend\View\Helper\Navigation\Breadcrumbs' => Breadcrumbs::class,
            'Zend\View\Helper\Navigation\Links'       => Links::class,
            'Zend\View\Helper\Navigation\Menu'        => Menu::class,
            'Zend\View\Helper\Navigation\Sitemap'     => Sitemap::class,
            // @codingStandardsIgnoreEnd

            // v2 normalized FQCNs
            'zendviewhelpernavigationbreadcrumbs' => Breadcrumbs::class,
            'zendviewhelpernavigationlinks'       => Links::class,
            'zendviewhelpernavigationmenu'        => Menu::class,
            'zendviewhelpernavigationsitemap'     => Sitemap::class,
        ];
    }

    /** @return FactoriesConfigurationType */
    public static function defaultViewHelperFactories(): array
    {
        return [
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
    }
}
