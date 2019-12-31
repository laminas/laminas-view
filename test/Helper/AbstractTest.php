<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\Helper;

use LaminasTest\View\Helper\TestAsset\ConcreteHelper;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @category   Laminas
 * @package    Laminas_View
 * @subpackage UnitTests
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class AbstractTest extends TestCase
{
    /**
     * @var ConcreteHelper
     */
    protected $helper;

    public function setUp()
    {
        $this->helper = new ConcreteHelper();
    }

    public function testViewSettersGetters()
    {
        $viewMock = $this->getMock('Laminas\View\Renderer\RendererInterface');

        $this->helper->setView($viewMock);
        $this->assertEquals($viewMock, $this->helper->getView());
    }
}
