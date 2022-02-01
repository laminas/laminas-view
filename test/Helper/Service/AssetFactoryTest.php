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

    public function testInvalidConfiguration(): void
    {
        $config   = [
            'view_helper_config' => [
                'asset' => [],
            ],
        ];
        $services = $this->getServices($config);

        $assetFactory = new AssetFactory();

        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('Invalid resource map configuration');
        $assetFactory($services, '');
    }

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
