<?php

declare(strict_types=1);

namespace LaminasTest\View;

use Generator;
use Laminas\Mvc\Controller\PluginManager as ControllerPluginManager;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\ServiceManager;
use Laminas\ServiceManager\Test\CommonPluginManagerTrait;
use Laminas\View\Exception\InvalidHelperException;
use Laminas\View\HelperPluginManager;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionProperty;

use function class_exists;
use function strpos;

class HelperPluginManagerCompatibilityTest extends TestCase
{
    use CommonPluginManagerTrait;

    protected static function getPluginManager(): HelperPluginManager
    {
        $factories = [];

        if (class_exists(ControllerPluginManager::class)) {
            // @codingStandardsIgnoreLine
            $factories['ControllerPluginManager'] = static fn(ContainerInterface $services): ControllerPluginManager => new ControllerPluginManager($services, [
                'invokables' => [
                    'flashmessenger' => FlashMessenger::class,
                ],
            ]);
        }

        $config  = new Config([
            'services'  => [
                'config' => [],
            ],
            'factories' => $factories,
        ]);
        $manager = new ServiceManager();
        $config->configureServiceManager($manager);
        return new HelperPluginManager($manager);
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
            // Skipping conditionally since it depends on laminas-mvc
            if (! class_exists(ControllerPluginManager::class) && strpos($target, '\\FlashMessenger') !== false) {
                continue;
            }

            // Skipping conditionally since it depends on laminas-mvc
            if (! class_exists(ControllerPluginManager::class) && strpos($target, '\\Url') !== false) {
                continue;
            }

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
