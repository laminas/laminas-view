<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\Helper\Navigation;

use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\ServiceManager;
use Laminas\ServiceManager\Test\CommonPluginManagerTrait;
use Laminas\View\Exception\InvalidHelperException;
use Laminas\View\Helper\Navigation\AbstractHelper;
use Laminas\View\Helper\Navigation\PluginManager;

/**
 * @group      Laminas_View
 */
class PluginManagerCompatibilityTest extends \PHPUnit_Framework_TestCase
{
    use CommonPluginManagerTrait;

    protected function getPluginManager()
    {
        return new PluginManager(new ServiceManager());
    }

    protected function getV2InvalidPluginException()
    {
        return InvalidHelperException::class;
    }

    protected function getInstanceOf()
    {
        return AbstractHelper::class;
    }

    /**
     * @group 43
     */
    public function testConstructorArgumentsAreOptionalUnderV2()
    {
        $helpers = $this->getPluginManager();
        if (method_exists($helpers, 'configure')) {
            $this->markTestSkipped('laminas-servicemanager v3 plugin managers require a container argument');
        }

        $helpers = new PluginManager();
        $this->assertInstanceOf(PluginManager::class, $helpers);
    }

    /**
     * @group 43
     */
    public function testConstructorAllowsConfigInstanceAsFirstArgumentUnderV2()
    {
        $helpers = $this->getPluginManager();
        if (method_exists($helpers, 'configure')) {
            $this->markTestSkipped('laminas-servicemanager v3 plugin managers require a container argument');
        }

        $helpers = new PluginManager(new Config([]));
        $this->assertInstanceOf(PluginManager::class, $helpers);
    }
}
