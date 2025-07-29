<?php

declare(strict_types=1);

namespace LaminasTest\View\Factory;

use Laminas\View\Factory\HelperPluginManagerFactory;
use LaminasTest\View\TestAsset\InMemoryContainer;
use PHPUnit\Framework\TestCase;

use function strrev;

final class HelperPluginManagerFactoryTest extends TestCase
{
    public function testThatHelpersRegisteredInConfigurationAreAvailableInTheHelperManager(): void
    {
        $helper = static fn (string $input): string => strrev($input);

        $config = [
            'view_helpers' => [
                'services' => [
                    'rev' => $helper,
                ],
            ],
        ];

        $container = new InMemoryContainer();
        $container->set('config', $config);

        $factory = new HelperPluginManagerFactory();
        $manager = $factory->__invoke($container);

        self::assertSame($helper, $manager->get('rev'));
        self::assertSame('oof', $helper('foo'));
    }

    public function testConfigIsOptional(): void
    {
        $container = new InMemoryContainer();
        $factory   = new HelperPluginManagerFactory();

        $factory->__invoke($container);

        $this->expectNotToPerformAssertions();
    }
}
