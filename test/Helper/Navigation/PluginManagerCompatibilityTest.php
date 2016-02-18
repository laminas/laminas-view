<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\View;

use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Helper\Navigation\PluginManager;

/**
 * @group      Zend_View
 */
class PluginManagerCompatibilityTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->helpers = new PluginManager(new ServiceManager());
    }

    /**
     * @group 43
     */
    public function testConstructorArgumentsAreOptionalUnderV2()
    {
        if (method_exists($this->helpers, 'configure')) {
            $this->markTestSkipped('zend-servicemanager v3 plugin managers require a container argument');
        }

        $helpers = new PluginManager();
        $this->assertInstanceOf(PluginManager::class, $helpers);
    }

    /**
     * @group 43
     */
    public function testConstructorAllowsConfigInstanceAsFirstArgumentUnderV2()
    {
        if (method_exists($this->helpers, 'configure')) {
            $this->markTestSkipped('zend-servicemanager v3 plugin managers require a container argument');
        }

        $helpers = new PluginManager(new Config([]));
        $this->assertInstanceOf(PluginManager::class, $helpers);
    }
}
