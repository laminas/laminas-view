<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View\Helper\Navigation;

use Interop\Container\ContainerInterface;
use Zend\View\HelperPluginManager;

/**
 * Plugin manager implementation for navigation helpers
 *
 * Enforces that helpers retrieved are instances of
 * Navigation\HelperInterface. Additionally, it registers a number of default
 * helpers.
 */
class PluginManager extends HelperPluginManager
{
    /**
     * @var string Valid instance types.
     */
    protected $instanceOf = AbstractHelper::class;

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $config = [
        'invokables' => [
            'breadcrumbs' => Breadcrumbs::class,
            'links'       => Links::class,
            'menu'        => Menu::class,
            'sitemap'     => Sitemap::class,
        ],
    ];

    /**
     * @param ContainerInterface $container
     * @param array $config
     */
    public function __construct(ContainerInterface $container, array $config = [])
    {
        $this->config['initializers'] = [
            function ($container, $instance) {
                if (! $instance instanceof AbstractHelper) {
                    continue;
                }

                $instance->setServiceLocator($container);
            },
        ];

        parent::__construct($container, $config);
    }
}
