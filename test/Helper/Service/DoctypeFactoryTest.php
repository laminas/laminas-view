<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\Service;

use Laminas\View\Helper\Doctype;
use Laminas\View\Helper\Service\DoctypeFactory;
use LaminasTest\View\TestAsset\InMemoryContainer;
use PHPUnit\Framework\TestCase;

final class DoctypeFactoryTest extends TestCase
{
    public function testServiceIsCreatedWithDefaultsWhenNoConfigurationIsAvailable(): void
    {
        $container = new InMemoryContainer();

        $factory = new DoctypeFactory();
        $service = $factory($container);

        self::assertTrue($service->isHtml5());
    }

    public function testFactorySetsDoctypeBasedOnConfig(): void
    {
        $config    = ['view_helper_config' => ['doctype' => Doctype::XHTML1_STRICT]];
        $container = new InMemoryContainer();
        $container->set('config', $config);

        $factory = new DoctypeFactory();
        $service = $factory($container);

        self::assertTrue($service->isXhtml());
    }

    public function testFactorySetsDoctypeBasedOnMvcConfig(): void
    {
        $config    = ['view_manager' => ['doctype' => Doctype::XHTML1_STRICT]];
        $container = new InMemoryContainer();
        $container->set('config', $config);

        $factory = new DoctypeFactory();
        $service = $factory($container);

        self::assertTrue($service->isXhtml());
    }

    public function testMezzioConfigOverridesMvcConfig(): void
    {
        $config = [
            'view_manager'       => ['doctype' => Doctype::XHTML1_STRICT],
            'view_helper_config' => ['doctype' => Doctype::HTML5],
        ];

        $container = new InMemoryContainer();
        $container->set('config', $config);

        $factory = new DoctypeFactory();
        $service = $factory($container);

        self::assertFalse($service->isXhtml());
    }
}
