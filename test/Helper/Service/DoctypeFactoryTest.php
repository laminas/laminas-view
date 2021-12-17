<?php

namespace LaminasTest\View\Helper\Service;

use Laminas\View\Helper\Doctype;
use Laminas\View\Helper\Service\DoctypeFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class DoctypeFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        Doctype::unsetDoctypeRegistry();
    }

    public function testServiceIsCreatedOnInvocation(): void
    {
        $container = $this->createContainer();

        $factory = new DoctypeFactory();
        $service = $factory($container);

        self::assertInstanceOf(Doctype::class, $service);
    }

    public function testFactorySetsDoctypeBasedOnConfig(): void
    {
        $config    = ['view_helper_config' => ['doctype' => Doctype::XHTML1_STRICT]];
        $container = $this->createContainer($config);

        $factory = new DoctypeFactory();
        $service = $factory($container);

        self::assertSame(Doctype::XHTML1_STRICT, $service->getDoctype());
    }

    public function testDefaultDoctypeIsUsedIfConfigIsMissing(): void
    {
        $config    = ['view_helper_config' => []];
        $container = $this->createContainer($config);

        $factory = new DoctypeFactory();
        $service = $factory($container);

        self::assertSame(Doctype::HTML4_LOOSE, $service->getDoctype());
    }

    /**
     * @param array<string, mixed> $config
     * @return ContainerInterface & MockObject
     */
    private function createContainer(array $config = [])
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->willReturn(true);
        $container->method('get')->with('config')->willReturn($config);
        return $container;
    }
}
