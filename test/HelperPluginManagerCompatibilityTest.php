<?php

declare(strict_types=1);

namespace LaminasTest\View;

use Generator;
use Laminas\ServiceManager\ServiceManager;
use Laminas\ServiceManager\Test\CommonPluginManagerTrait;
use Laminas\View\ConfigProvider;
use Laminas\View\Exception\InvalidHelperException;
use Laminas\View\HelperPluginManager;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

final class HelperPluginManagerCompatibilityTest extends TestCase
{
    use CommonPluginManagerTrait;

    protected static function getPluginManager(): HelperPluginManager
    {
        $provider = new ConfigProvider();
        $config   = $provider->__invoke();
        /** @psalm-suppress MixedArrayAssignment */
        $config['dependencies']['services'] = ['config' => $config];

        /** @psalm-suppress MixedArgument */
        $serviceManager = new ServiceManager($config['dependencies']);
        return new HelperPluginManager($serviceManager);
    }

    protected function getV2InvalidPluginException(): string
    {
        return InvalidHelperException::class;
    }

    /**
     * @psalm-return Generator<string, array{0: string, 1: string}, mixed, void>
     */
    public static function aliasProvider(): Generator
    {
        $pluginManager = self::getPluginManager();
        $r             = new ReflectionProperty($pluginManager, 'aliases');
        $aliases       = $r->getValue($pluginManager);
        self::assertIsArray($aliases);

        foreach ($aliases as $alias => $target) {
            self::assertIsString($target);
            self::assertIsString($alias);

            yield $alias => [$alias, $target];
        }
    }

    public function getInstanceOf(): void
    {
        // no-op; instanceof is not used in this implementation
    }

    public function testInstanceOfMatches(): void
    {
        $this->markTestSkipped('instanceOf is not used with this implementation');
    }
}
