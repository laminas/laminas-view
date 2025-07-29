<?php

declare(strict_types=1);

namespace LaminasTest\View;

use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Helper\Doctype;
use Laminas\View\Helper\HeadStyle;
use Laminas\View\Helper\HeadTitle;
use Laminas\View\Helper\HelperInterface;
use Laminas\View\Helper\Partial;
use Laminas\View\HelperPluginManager;
use PHPUnit\Framework\TestCase;

final class HelperPluginManagerTest extends TestCase
{
    private HelperPluginManager $helpers;

    protected function setUp(): void
    {
        $container     = GenerateServiceManager::withConfig([]);
        $this->helpers = $container->get(HelperPluginManager::class);
    }

    public function testRegisteringInvalidHelperRaisesInvalidServiceException(): void
    {
        $helpers = new HelperPluginManager(new ServiceManager(), [
            'factories' => [
                'test' => fn() => $this,
            ],
        ]);
        $this->expectException(InvalidServiceException::class);
        $helpers->get('test');
    }

    public function testRequestingAnUnregisteredHelperRaisesServiceNotFoundException(): void
    {
        $helpers = new HelperPluginManager(new ServiceManager(), []);
        $this->expectException(ServiceNotFoundException::class);
        $helpers->get('test');
    }

    public function testCanOverrideAFactoryViaConfigurationPassedToConstructor(): void
    {
        $helper  = $this->createMock(HelperInterface::class);
        $helpers = new HelperPluginManager(new ServiceManager(), [
            'factories' => [
                Partial::class => static fn(): HelperInterface => $helper,
            ],
        ]);
        $this->assertSame($helper, $helpers->get(Partial::class));
    }

    public function testCanUseCallableAsHelper(): void
    {
        $helper  = static function (): void {
        };
        $helpers = new HelperPluginManager(new ServiceManager(), [
            'factories' => [
                'foo' => static fn(): callable => $helper,
            ],
        ]);
        $this->assertSame($helper, $helpers->get('foo'));
    }

    public function testDoctypeFactoryExists(): void
    {
        self::assertTrue($this->helpers->has(Doctype::class));
    }

    public function testThatStatefulHelpersCanBeResetEnMasse(): void
    {
        $headTitle = $this->helpers->get(HeadTitle::class);
        $headTitle->set('Something');

        $headStyle = $this->helpers->get(HeadStyle::class);
        $headStyle->appendStyle('.foo { color: pink; }');

        self::assertNotEmpty($headTitle->renderTitle());
        self::assertNotEmpty($headStyle->toString());

        $this->helpers->resetState();

        self::assertSame('', $headTitle->renderTitle());
        self::assertSame('', $headStyle->toString());
    }
}
