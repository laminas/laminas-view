<?php

declare(strict_types=1);

namespace LaminasTest\View;

use Laminas\View\ConfigProvider;
use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{
    public function testThatInvokeReturnsANonEmptyArray(): void
    {
        self::assertNotEmpty((new ConfigProvider())->__invoke());
    }

    public function testThatConventionalMethodForDependencyConfigurationExists(): void
    {
        $provider = new ConfigProvider();
        self::assertNotEmpty($provider->getDependencyConfig());
    }
}
