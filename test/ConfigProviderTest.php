<?php

declare(strict_types=1);

namespace LaminasTest\View;

use Laminas\Escaper\Escaper;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\ConfigProvider;
use PHPUnit\Framework\TestCase;

final class ConfigProviderTest extends TestCase
{
    public function testEscaperCanBeRetrieved(): void
    {
        $deps      = (new ConfigProvider())->getDependencies();
        $container = new ServiceManager($deps);
        $container->get(Escaper::class);
        $this->expectNotToPerformAssertions();
    }
}
