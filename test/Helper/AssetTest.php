<?php

namespace LaminasTest\View\Helper;

use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Exception;
use Laminas\View\Helper\Asset;
use Laminas\View\HelperPluginManager;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class AssetTest extends TestCase
{
    use ProphecyTrait;

    /** @var array */
    protected $resourceMap = [
        'css/style.css' => 'css/style-3a97ff4ee3.css',
        'js/vendor.js' => 'js/vendor-a507086eba.js',
    ];

    /** @var Asset */
    protected $asset;

    protected function setUp(): void
    {
        parent::setUp();

        $this->asset = new Asset();
        $this->asset->setResourceMap($this->resourceMap);
    }

    public function testHelperPluginManagerReturnsAssetHelper(): void
    {
        $helpers = $this->getHelperPluginManager();
        $asset = $helpers->get('asset');

        $this->assertInstanceOf(Asset::class, $asset);
    }

    public function testHelperPluginManagerReturnsAssetHelperByClassName(): void
    {
        $helpers = $this->getHelperPluginManager();
        $asset = $helpers->get(Asset::class);

        $this->assertInstanceOf(Asset::class, $asset);
    }

    public function testInvalidAssetName(): void
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
     *
     * @return void
     */
    public function testInvokeResult($name, $expected): void
    {
        $result = $this->asset->__invoke($name);

        $this->assertEquals($expected, $result);
    }

    /**
     * @return ((int|string)|mixed)[][]
     *
     * @psalm-return list<array{0: array-key, 1: mixed}>
     */
    public function assets(): array
    {
        $data = [];
        foreach ($this->resourceMap as $key => $value) {
            $data[] = [$key, $value];
        }
        return $data;
    }

    protected function getHelperPluginManager(array $config = []): HelperPluginManager
    {
        $services = $this->prophesize(ServiceManager::class);
        $services->get('config')->willReturn($config);

        return new HelperPluginManager($services->reveal());
    }
}
