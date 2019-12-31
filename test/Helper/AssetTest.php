<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\Helper;

use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Exception;
use Laminas\View\Helper\Asset;
use Laminas\View\HelperPluginManager;
use PHPUnit\Framework\TestCase;

class AssetTest extends TestCase
{
    /** @var array */
    protected $resourceMap = [
        'css/style.css' => 'css/style-3a97ff4ee3.css',
        'js/vendor.js' => 'js/vendor-a507086eba.js',
    ];

    /** @var Asset */
    protected $asset;

    protected function setUp()
    {
        parent::setUp();

        $this->asset = new Asset();
        $this->asset->setResourceMap($this->resourceMap);
    }

    public function testHelperPluginManagerReturnsAssetHelper()
    {
        $helpers = $this->getHelperPluginManager();
        $asset = $helpers->get('asset');

        $this->assertInstanceOf(Asset::class, $asset);
    }

    public function testHelperPluginManagerReturnsAssetHelperByClassName()
    {
        $helpers = $this->getHelperPluginManager();
        $asset = $helpers->get(Asset::class);

        $this->assertInstanceOf(Asset::class, $asset);
    }

    public function testInvalidAssetName()
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Asset is not defined');

        $this->asset->__invoke('unknown');
    }

    /**
     * @dataProvider assets
     *
     * @param string $name
     * @param string $expected
     */
    public function testInvokeResult($name, $expected)
    {
        $result = $this->asset->__invoke($name);

        $this->assertEquals($expected, $result);
    }

    public function assets()
    {
        $data = [];
        foreach ($this->resourceMap as $key => $value) {
            $data[] = [$key, $value];
        }
        return $data;
    }

    protected function getHelperPluginManager(array $config = [])
    {
        $services = $this->prophesize(ServiceManager::class);
        $services->get('config')->willReturn($config);

        return new HelperPluginManager($services->reveal());
    }
}
