<?php

namespace LaminasTest\View\Helper\Navigation;

use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\ServiceManager;
use Laminas\ServiceManager\Test\CommonPluginManagerTrait;
use Laminas\View\Exception\InvalidHelperException;
use Laminas\View\Helper\Navigation\AbstractHelper;
use Laminas\View\Helper\Navigation\Breadcrumbs;
use Laminas\View\Helper\Navigation\PluginManager;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_View
 */
class PluginManagerCompatibilityTest extends TestCase
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
    public function testConstructorArgumentsAreOptionalUnderV2(): void
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
    public function testConstructorAllowsConfigInstanceAsFirstArgumentUnderV2(): void
    {
        $helpers = $this->getPluginManager();
        if (method_exists($helpers, 'configure')) {
            $this->markTestSkipped('laminas-servicemanager v3 plugin managers require a container argument');
        }

        $helpers = new PluginManager(new Config([]));
        $this->assertInstanceOf(PluginManager::class, $helpers);
    }

    public function testInjectsParentContainerIntoHelpers(): void
    {
        $config = new Config([
            'navigation' => [
                'default' => [],
            ],
        ]);

        $services = new ServiceManager();
        $config->configureServiceManager($services);
        $helpers = new PluginManager($services);

        $helper = $helpers->get('breadcrumbs');
        $this->assertInstanceOf(Breadcrumbs::class, $helper);
        $this->assertSame($services, $helper->getServiceLocator());
    }

    /**
     * @todo remove this test once we set the minimum laminas-servicemanager version to 3
     */
    public function testRegisteringInvalidElementRaisesException(): void
    {
        $this->expectException($this->getServiceNotFoundException());
        $this->getPluginManager()->setService('test', $this);
    }

    /**
     * @todo remove this test once we set the minimum laminas-servicemanager version to 3
     */
    public function testLoadingInvalidElementRaisesException(): void
    {
        $manager = $this->getPluginManager();
        $manager->setInvokableClass('test', get_class($this));
        $this->expectException($this->getServiceNotFoundException());
        $manager->get('test');
    }
}
