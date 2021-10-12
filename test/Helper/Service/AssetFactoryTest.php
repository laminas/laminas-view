<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\Helper\Service;

use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Exception;
use Laminas\View\Helper\Asset;
use Laminas\View\Helper\Service\AssetFactory;
use Laminas\View\HelperPluginManager;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class AssetFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testAssetFactoryCreateServiceCreatesAssetInstance(): void
    {
        $services = $this->getServices();

        $assetFactory = new AssetFactory();
        $asset = $assetFactory->createService($services);

        $this->assertInstanceOf(Asset::class, $asset);
    }

    public function testAssetFactoryInvokableCreatesAssetInstance(): void
    {
        $services = $this->getServices();

        $assetFactory = new AssetFactory();
        $asset = $assetFactory($services, '');

        $this->assertInstanceOf(Asset::class, $asset);
    }

    public function testValidConfiguration(): void
    {
        $config = [
            'view_helper_config' => [
                'asset' => [
                    'resource_map' => [
                        'css/style.css' => 'css/style-3a97ff4ee3.css',
                        'js/vendor.js' => 'js/vendor-a507086eba.js',
                    ],
                ],
            ],
        ];

        $services = $this->getServices($config);
        $assetFactory = new AssetFactory();

        $asset = $assetFactory($services, '');

        $this->assertEquals($config['view_helper_config']['asset']['resource_map'], $asset->getResourceMap());
    }

    public function testInvalidConfiguration(): void
    {
        $config = [
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

    protected function getServices(array $config = []): ServiceManager
    {
        $services = $this->prophesize(ServiceManager::class);
        $services->get('config')->willReturn($config);

        $helpers = new HelperPluginManager($services->reveal());

        // test if we are using Laminas\ServiceManager v3
        if (method_exists($helpers, 'configure')) {
            return $services->reveal();
        }

        return $helpers;
    }
}
