<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View;

use Laminas\Mvc\Controller\Plugin\FlashMessenger as V2FlashMessenger;
use Laminas\Mvc\Controller\PluginManager as ControllerPluginManager;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\ServiceManager;
use Laminas\ServiceManager\Test\CommonPluginManagerTrait;
use Laminas\View\Exception\InvalidHelperException;
use Laminas\View\HelperPluginManager;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class HelperPluginManagerCompatibilityTest extends TestCase
{
    use CommonPluginManagerTrait;

    protected function getPluginManager()
    {
        $factories = [];

        if (class_exists(ControllerPluginManager::class)) {
            $factories['ControllerPluginManager'] = function ($services, $name, $options) {
                return new ControllerPluginManager($services, [
                    'invokables' => [],
                ]);
            };
        }

        $config = new Config([
            'services' => [
                'config' => [],
            ],
            'factories' => $factories,
        ]);
        $manager = new ServiceManager();
        $config->configureServiceManager($manager);
        $helperManager = new HelperPluginManager($manager);

        return $helperManager;
    }

    protected function getV2InvalidPluginException()
    {
        return InvalidHelperException::class;
    }

    public function aliasProvider()
    {
        $pluginManager = $this->getPluginManager();
        $r = new ReflectionProperty($pluginManager, 'aliases');
        $r->setAccessible(true);
        $aliases = $r->getValue($pluginManager);

        foreach ($aliases as $alias => $target) {
            // Skipping conditionally since it depends on laminas-mvc
            if (! class_exists(ControllerPluginManager::class) && strpos($target, '\\Url')) {
                continue;
            }

            yield $alias => [$alias, $target];
        }
    }

    public function getInstanceOf()
    {
        // no-op; instanceof is not used in this implementation
    }

    public function testInstanceOfMatches()
    {
        $this->markTestSkipped('instanceOf is not used with this implementation');
    }

    /**
     * @todo remove this test once we set the minimum laminas-servicemanager version to 3
     */
    public function testRegisteringInvalidElementRaisesException()
    {
        $this->expectException($this->getServiceNotFoundException());
        $this->getPluginManager()->setService('test', $this);
    }

    /**
     * @todo remove this test once we set the minimum laminas-servicemanager version to 3
     */
    public function testLoadingInvalidElementRaisesException()
    {
        $manager = $this->getPluginManager();
        $manager->setInvokableClass('test', get_class($this));
        $this->expectException($this->getServiceNotFoundException());
        $manager->get('test');
    }
}
