<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\Service;

use Laminas\ServiceManager\ServiceManager;
use Laminas\View\ConfigProvider;
use Laminas\View\Helper\DeclareVars;
use Laminas\View\Helper\Service\DeclareVarsFactory;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;
use LaminasTest\View\TestAsset\InMemoryContainer;
use PHPUnit\Framework\TestCase;

final class DeclareVarsFactoryTest extends TestCase
{
    public function testFactory(): void
    {
        $container = new InMemoryContainer();
        $renderer  = new PhpRenderer();
        $container->set(PhpRenderer::class, $renderer);

        $factory = new DeclareVarsFactory();
        $helper  = $factory->__invoke($container);

        $helper->__invoke(['foo' => 'bar']);

        self::assertSame('bar', $renderer->vars()->foo ?? null);
    }

    public function testDeclareVarsCanBeRetrievedFromThePluginManagerWithTheDefaultConfiguration(): void
    {
        $config                             = (new ConfigProvider())->__invoke();
        $config['dependencies']['services'] = ['config' => $config];

        $container = new ServiceManager($config['dependencies']);
        $helpers   = $container->get(HelperPluginManager::class);
        $helper    = $helpers->get(DeclareVars::class);
        $renderer  = $container->get(PhpRenderer::class);

        $helper->__invoke(['foo' => 'bar']);

        self::assertSame('bar', $renderer->vars()->foo ?? null);
    }
}
