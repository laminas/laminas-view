<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\Service;

use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Exception;
use Laminas\View\Helper\Asset;
use Laminas\View\Helper\Service\AssetFactory;
use PHPUnit\Framework\TestCase;

class AssetFactoryTest extends TestCase
{
    /**
     * @deprecated for removal in 3.0
     */
    public function testAssetFactoryCreateServiceCreatesAssetInstance(): void
    {
        $services = $this->getServices();

        $assetFactory = new AssetFactory();
        $asset        = $assetFactory->createService($services);

        $this->assertInstanceOf(Asset::class, $asset);
    }

    public function testAssetFactoryInvokableCreatesAssetInstance(): void
    {
        $services = $this->getServices();

        $assetFactory = new AssetFactory();
        $asset        = $assetFactory($services, '');

        $this->assertInstanceOf(Asset::class, $asset);
    }

    /**
     * @deprecated for removal in 3.0
     */
    public function testValidConfiguration(): void
    {
        $config = [
            'view_helper_config' => [
                'asset' => [
                    'resource_map' => [
                        'css/style.css' => 'css/style-3a97ff4ee3.css',
                        'js/vendor.js'  => 'js/vendor-a507086eba.js',
                    ],
                ],
            ],
        ];

        $services     = $this->getServices($config);
        $assetFactory = new AssetFactory();

        $asset = $assetFactory($services, '');

        $this->assertEquals($config['view_helper_config']['asset']['resource_map'], $asset->getResourceMap());
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

        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage(
            'Invalid resource map configuration. Expected the key '
            . '"resource_map" to contain an array value but received "string"'
        );
        (new AssetFactory())($container, '');
    }

    /**
     * @return array<string, array{0: array<string, mixed>}>
     */
    public function validConfigProvider(): array
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

    /**
     * @dataProvider validConfigProvider
     * @param array<string, mixed> $config
     */
    public function testThatAnExceptionWillNotBeThrownWhenGivenUnsetOrEmptyArrayConfiguration(array $config): void
    {
        $container = $this->getServices($config);
        (new AssetFactory())($container, 'foo');
        self::assertTrue(true);
    }

    /** @param array<string, mixed> $config */
    private function getServices(array $config = []): ServiceManager
    {
        $services = $this->createMock(ServiceManager::class);
        $services->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn($config);

        return $services;
    }
}
