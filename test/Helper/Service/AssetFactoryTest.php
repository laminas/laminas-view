<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\Service;

use ArrayObject;
use Laminas\View\Exception\RuntimeException;
use Laminas\View\Helper\Asset;
use Laminas\View\Helper\Service\AssetFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class AssetFactoryTest extends TestCase
{
    public function testAssetFactoryInvokableCreatesAssetInstance(): void
    {
        $services = $this->getServices();

        $assetFactory = new AssetFactory();
        $asset        = $assetFactory($services);

        $this->assertInstanceOf(Asset::class, $asset);
    }

    public function testThatAnExceptionWillBeThrownWhenTheResourceMapIsSetToANonArray(): void
    {
        $container = $this->getServices([
            'view_helper_config' => [
                'asset' => [
                    'resource_map' => 'Not an array',
                ],
            ],
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Invalid resource map configuration. Expected the key '
            . '"resource_map" to contain an array value but received "string"',
        );
        (new AssetFactory())($container);
    }

    /**
     * @return array<string, array{0: array<string, mixed>}>
     */
    public static function validConfigProvider(): array
    {
        return [
            'No View Helper Configuration' => [
                [],
            ],
            'No Asset Config At All'       => [
                [
                    'view_helper_config' => [],
                ],
            ],
            'No Resource Map Key'          => [
                [
                    'view_helper_config' => [
                        'asset' => [],
                    ],
                ],
            ],
            'Empty Resource Map'           => [
                [
                    'view_helper_config' => [
                        'asset' => [
                            'resource_map' => [],
                        ],
                    ],
                ],
            ],
            'Non-Empty Resource Map'       => [
                [
                    'view_helper_config' => [
                        'asset' => [
                            'resource_map' => [
                                'foo.css' => 'assets/foo.1.css',
                                'bar.css' => 'assets/bar.1.css',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /** @return iterable<string, array{0: ArrayObject<string, mixed>}> */
    public static function validConfigAsArrayObjectProvider(): iterable
    {
        foreach (self::validConfigProvider() as $key => $args) {
            yield $key . ' (ArrayObject)' => [new ArrayObject($args[0])];
        }
    }

    /** @param iterable<string, mixed> $config */
    #[DataProvider('validConfigProvider')]
    #[DataProvider('validConfigAsArrayObjectProvider')]
    public function testThatAnExceptionWillNotBeThrownWhenGivenUnsetOrEmptyArrayConfiguration(iterable $config): void
    {
        $container = $this->getServices($config);
        (new AssetFactory())($container);
        self::assertTrue(true);
    }

    /** @param iterable<string, mixed> $config */
    private function getServices(iterable $config = []): ContainerInterface
    {
        $services = $this->createMock(ContainerInterface::class);
        $services->expects(self::once())
            ->method('has')
            ->with('config')
            ->willReturn(true);
        $services->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn($config);

        return $services;
    }

    public function testAllConfiguredAssetsCanBeRetrieved(): void
    {
        $container = $this->getServices([
            'view_helper_config' => [
                'asset' => [
                    'resource_map' => [
                        'foo.css' => 'assets/foo.1.css',
                        'bar.css' => 'assets/bar.1.css',
                    ],
                ],
            ],
        ]);

        $helper = (new AssetFactory())($container);

        self::assertSame('assets/foo.1.css', $helper->__invoke('foo.css'));
        self::assertSame('assets/bar.1.css', $helper->__invoke('bar.css'));
    }
}
