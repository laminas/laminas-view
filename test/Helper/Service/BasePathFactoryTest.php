<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\Service;

use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Exception\RuntimeException;
use Laminas\View\Helper\BasePath;
use Laminas\View\Helper\Service\BasePathFactory;
use Laminas\View\HelperPluginManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class BasePathFactoryTest extends TestCase
{
    private ServiceManager $container;

    protected function setUp(): void
    {
        $this->container = new ServiceManager();
    }

    /** @return array<string, array{0: array, 1: string, 2: string|null}> */
    public static function configDataProvider(): array
    {
        return [
            'Empty Config'                   => [[], 'foo', null],
            'Base Path Config Missing (Old)' => [['view_manager' => []], 'foo', null],
            'Base Path Config Missing (New)' => [['view_helper_config' => []], 'foo', null],
            'Base Path Config Null (Old)'    => [['view_manager' => ['base_path' => null]], 'foo', null],
            'Base Path Config Null (New)'    => [['view_helper_config' => ['base_path' => null]], 'foo', null],
            'Base Path Config Set (Old)'     => [['view_manager' => ['base_path' => '/foo']], 'foo', '/foo/foo'],
            'Base Path Config Set (New)'     => [['view_helper_config' => ['base_path' => '/foo']], 'foo', '/foo/foo'],
        ];
    }

    #[DataProvider('configDataProvider')]
    public function testFactoryWithVariousConfigurationSetups(array $config, string $input, string|null $expect): void
    {
        $this->container->setService('config', $config);

        $helper = (new BasePathFactory())($this->container);

        if ($expect === null) {
            $this->expectException(RuntimeException::class);
            $helper->__invoke($input);

            return;
        }

        self::assertSame($expect, $helper->__invoke($input));
    }

    public function testHelperWillBeReturnedWhenThereIsNoConfigurationAtAll(): void
    {
        self::assertFalse($this->container->has('config'));
        (new BasePathFactory())($this->container);
    }

    public function testThatTheBasePathFactoryIsWiredUpByDefault(): void
    {
        $manager = new HelperPluginManager(new ServiceManager());
        self::assertInstanceOf(BasePath::class, $manager->get(BasePath::class));
    }
}
