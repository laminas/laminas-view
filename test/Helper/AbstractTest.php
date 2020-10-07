<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

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

    /**
     * @var ConcreteHelper
     */
    protected $helper;

    protected function setUp(): void
    {
        $this->helper = new ConcreteHelper();
    }

    public function testViewSettersGetters()
    {
        $viewMock = $this->prophesize(RendererInterface::class)->reveal();

        $this->helper->setView($viewMock);
        $this->assertEquals($viewMock, $this->helper->getView());
    }
}
