<?php

declare(strict_types=1);

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

    protected function getPluginManager(): PluginManager
    {
        return new PluginManager(new ServiceManager());
    }

    protected function getV2InvalidPluginException(): string
    {
        return InvalidHelperException::class;
    }

    protected function getInstanceOf(): string
    {
        return AbstractHelper::class;
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
}
