<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\Service;

use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Helper\BasePath;
use Laminas\View\Helper\Service\BasePathFactory;
use Laminas\View\HelperPluginManager;
use PHPUnit\Framework\TestCase;

class BasePathFactoryTest extends TestCase
{
    private ServiceManager $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new ServiceManager();
    }

    /** @return array<string, array{0: array}> */
    public function configDataProvider(): array
    {
        return [
            'Empty Config'             => [[]],
            'Base Path Config Missing' => [['view_manager' => []]],
            'Base Path Config Null'    => [['view_manager' => ['base_path' => null]]],
            'Base Path Config Set'     => [['view_manager' => ['base_path' => '/foo']]],
        ];
    }

    /** @dataProvider configDataProvider */
    public function testFactoryWithVariousConfigurationSetups(array $config): void
    {
        $this->container->setService('config', $config);

        $helper = (new BasePathFactory())($this->container);
        self::assertInstanceOf(BasePath::class, $helper);
    }

    public function testHelperWillBeReturnedWhenThereIsNoConfigurationAtAll(): void
    {
        self::assertFalse($this->container->has('config'));
        $helper = (new BasePathFactory())($this->container);
        self::assertInstanceOf(BasePath::class, $helper);
    }

    public function testThatTheBasePathFactoryIsWiredUpByDefault(): void
    {
        $manager = new HelperPluginManager(new ServiceManager());
        self::assertInstanceOf(BasePath::class, $manager->get(BasePath::class));
    }
}
