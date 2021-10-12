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
            // @codingStandardsIgnoreLine
            $factories['ControllerPluginManager'] = function ($services, $name, $options): \Laminas\Mvc\Controller\PluginManager {
                return new ControllerPluginManager($services, [
                    'invokables' => [
                        'flashmessenger' => class_exists(FlashMessenger::class)
                            ? FlashMessenger::class
                            : V2FlashMessenger::class,
                    ],
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

    /**
     * @return \Generator
     *
     * @psalm-return \Generator<mixed, array{0: mixed, 1: mixed}, mixed, void>
     */
    public function aliasProvider(): \Generator
    {
        $pluginManager = $this->getPluginManager();
        $r = new ReflectionProperty($pluginManager, 'aliases');
        $r->setAccessible(true);
        $aliases = $r->getValue($pluginManager);

        foreach ($aliases as $alias => $target) {
            // Skipping conditionally since it depends on laminas-mvc
            if (! class_exists(ControllerPluginManager::class) && strpos($target, '\\FlashMessenger')) {
                continue;
            }

            // Skipping conditionally since it depends on laminas-mvc
            if (! class_exists(ControllerPluginManager::class) && strpos($target, '\\Url')) {
                continue;
            }

            yield $alias => [$alias, $target];
        }
    }

    /**
     * @return void
     */
    public function getInstanceOf()
    {
        // no-op; instanceof is not used in this implementation
    }

    /**
     * @return never
     */
    public function testInstanceOfMatches()
    {
        $this->markTestSkipped('instanceOf is not used with this implementation');
    }

    /**
     * @todo remove this test once we set the minimum laminas-servicemanager version to 3
     *
     * @return void
     */
    public function testRegisteringInvalidElementRaisesException(): void
    {
        $this->expectException($this->getServiceNotFoundException());
        $this->getPluginManager()->setService('test', $this);
    }

    /**
     * @todo remove this test once we set the minimum laminas-servicemanager version to 3
     *
     * @return void
     */
    public function testLoadingInvalidElementRaisesException(): void
    {
        $manager = $this->getPluginManager();
        $manager->setInvokableClass('test', get_class($this));
        $this->expectException($this->getServiceNotFoundException());
        $manager->get('test');
    }
}
