<?php

declare(strict_types=1);

namespace LaminasTest\View;

use Generator;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\ConfigProvider;
use Laminas\View\Helper\HelperInterface;
use Laminas\View\HelperPluginManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClassConstant;
use stdClass;
use Throwable;

use function is_callable;

final class HelperPluginManagerCompatibilityTest extends TestCase
{
    private static function getPluginManager(): HelperPluginManager
    {
        $provider                           = new ConfigProvider();
        $config                             = $provider->__invoke();
        $config['dependencies']['services'] = ['config' => $config];
        $serviceManager                     = new ServiceManager($config['dependencies']);
        return $serviceManager->get(HelperPluginManager::class);
    }

    /**
     * Psalm really cannot infer, or be told the shape of the reflected array constant
     *
     * @return array{
     *     aliases: array<string, string>,
     *     factories: array<string, string>,
     * }
     * @psalm-suppress InvalidReturnStatement,InvalidReturnType
     */
    private static function fetchDefaultConfig(): array
    {
        $r      = new ReflectionClassConstant(HelperPluginManager::class, 'CONFIG');
        $config = $r->getValue();
        self::assertNotNull($config);
        self::assertIsArray($config);

        return $config;
    }

    /**
     * @psalm-return Generator<string, array{0: string, 1: string}, mixed, void>
     */
    public static function aliasProvider(): Generator
    {
        $config = self::fetchDefaultConfig();

        foreach ($config['factories'] as $alias => $target) {
            yield $alias => [$alias, $target];
        }

        foreach ($config['aliases'] as $alias => $target) {
            yield $alias => [$alias, $target];
        }
    }

    public function testRegisteringInvalidElementRaisesException(): void
    {
        $this->expectException($this->getServiceNotFoundException());
        self::getPluginManager()->configure([
            'services' => [
                'test' => $this,
            ],
        ]);
    }

    public function testLoadingInvalidElementRaisesException(): void
    {
        $manager = self::getPluginManager();
        $manager->configure([
            'invokables' => [
                'test' => stdClass::class,
            ],
        ]);
        $this->expectException($this->getServiceNotFoundException());
        $manager->get('test');
    }

    #[DataProvider('aliasProvider')]
    public function testPluginAliasesResolve(string $alias): void
    {
        $instance = self::getPluginManager()->get($alias);

        self::assertTrue(
            is_callable($instance) || $instance instanceof HelperInterface,
        );
    }

    /** @return class-string<Throwable> */
    protected function getServiceNotFoundException(): string
    {
        return InvalidServiceException::class;
    }
}
