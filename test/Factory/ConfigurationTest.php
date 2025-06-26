<?php

declare(strict_types=1);

namespace Factory;

use Laminas\View\Factory\Configuration;
use LaminasTest\View\TestAsset\InMemoryContainer;
use PHPUnit\Framework\TestCase;

final class ConfigurationTest extends TestCase
{
    public function testGetReturnsConfigWhenFound(): void
    {
        $container = new InMemoryContainer();
        $container->set('config', ['foo']);

        self::assertSame(['foo'], Configuration::get($container));
    }

    public function testGetReturnsEmptyArrayWhenConfigNotFound(): void
    {
        self::assertSame([], Configuration::get(new InMemoryContainer()));
    }

    public function testGetReturnsEmptyArrayWhenConfigNotAnArray(): void
    {
        $container = new InMemoryContainer();
        $container->set('config', 'foo');

        self::assertSame([], Configuration::get($container));
    }

    public function testCustomEncodingCanBeFoundInLegacyLocation(): void
    {
        $container = new InMemoryContainer();
        $container->set('config', [
            'view_manager' => [
                'encoding' => 'utf-16',
            ],
        ]);

        self::assertSame('utf-16', Configuration::viewEncoding($container));
    }

    public function testCustomEncodingOverridesLegacyLocation(): void
    {
        $container = new InMemoryContainer();
        $container->set('config', [
            'view_manager'       => [
                'encoding' => 'utf-16',
            ],
            'view_helper_config' => [
                'encoding' => 'iso-8859-1',
            ],
        ]);

        self::assertSame('iso-8859-1', Configuration::viewEncoding($container));
    }

    public function testDefaultEncodingReturnedWhenEncodingNotSet(): void
    {
        $container = new InMemoryContainer();
        $container->set('config', [
            'view_helper_config' => [
                'encoding' => null,
            ],
        ]);

        self::assertSame('utf-8', Configuration::viewEncoding($container));
    }
}
