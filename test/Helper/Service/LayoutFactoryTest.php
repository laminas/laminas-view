<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\Service;

use Laminas\View\Helper\Service\LayoutFactory;
use Laminas\View\HelperPluginManager;
use LaminasTest\View\TestAsset\InMemoryContainer;
use PHPUnit\Framework\TestCase;

final class LayoutFactoryTest extends TestCase
{
    public function testAnInstanceCanBeRetrieved(): void
    {
        $container = new InMemoryContainer();
        $container->set(HelperPluginManager::class, new HelperPluginManager($container));
        $factory = new LayoutFactory();
        $factory->__invoke($container);
        $this->expectNotToPerformAssertions();
    }
}
