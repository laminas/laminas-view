<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\Helper;

use Laminas\View\Renderer\PhpRenderer as View;
use Laminas\View\Helper;

/**
 * Test class for Laminas\View\Helper\Placeholder.
 *
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class PlaceholderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Helper\Placeholder
     */
    public $placeholder;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
        $this->placeholder = new Helper\Placeholder();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->placeholder);
    }

    /**
     * @return void
     */
    public function testSetView()
    {
        $view = new View();
        $this->placeholder->setView($view);
        $this->assertSame($view, $this->placeholder->getView());
    }

    /**
     * @return void
     */
    public function testPlaceholderRetrievesContainer()
    {
        $container = $this->placeholder->__invoke('foo');
        $this->assertInstanceOf('Laminas\View\Helper\Placeholder\Container\AbstractContainer', $container);
    }

    /**
     * @return void
     */
    public function testPlaceholderRetrievesSameContainerOnSubsequentCalls()
    {
        $container1 = $this->placeholder->__invoke('foo');
        $container2 = $this->placeholder->__invoke('foo');
        $this->assertSame($container1, $container2);
    }
}
