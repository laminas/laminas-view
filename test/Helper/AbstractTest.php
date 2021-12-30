<?php

namespace LaminasTest\View\Helper;

use Laminas\View\Renderer\RendererInterface;
use LaminasTest\View\Helper\TestAsset\ConcreteHelper;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class AbstractTest extends TestCase
{
    use ProphecyTrait;

    /** @var ConcreteHelper */
    protected $helper;

    protected function setUp(): void
    {
        $this->helper = new ConcreteHelper();
    }

    public function testViewSettersGetters(): void
    {
        $viewMock = $this->prophesize(RendererInterface::class)->reveal();

        $this->helper->setView($viewMock);
        $this->assertEquals($viewMock, $this->helper->getView());
    }
}
