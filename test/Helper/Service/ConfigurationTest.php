<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\Service;

use Laminas\View\Helper\Service\Configuration;
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

    public function testCustomEncodingCanBeFound(): void
    {
        $container = new InMemoryContainer();
        $container->set('config', [
            'view_manager' => [
                'encoding' => 'utf-16',
            ],
        ]);

        self::assertSame('utf-16', Configuration::viewEncoding($container));
    }

    public function testDefaultEncodingReturnedWhenEncodingNotSet(): void
    {
        $container = new InMemoryContainer();
        $container->set('config', [
            'view_manager' => [
                'encoding' => null,
            ],
        ]);

        self::assertSame('utf-8', Configuration::viewEncoding($container));
    }
}
