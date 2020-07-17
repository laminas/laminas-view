<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\Helper;

use Laminas\View\Helper;
use Laminas\View\Helper\Placeholder\Container\AbstractContainer;
use Laminas\View\Renderer\PhpRenderer as View;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Laminas\View\Helper\Placeholder.
 *
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class PlaceholderTest extends TestCase
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
    protected function setUp()
    {
        $this->placeholder = new Helper\Placeholder();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown()
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
    public function testContainerExists()
    {
        $this->placeholder->__invoke('foo');
        $containerExists = $this->placeholder->__invoke()->containerExists('foo');

        $this->assertTrue($containerExists);
    }

    /**
     * @return void
     */
    public function testPlaceholderRetrievesContainer()
    {
        $container = $this->placeholder->__invoke('foo');
        $this->assertInstanceOf(AbstractContainer::class, $container);
    }

    /**
     * @return void
     */
    public function testPlaceholderRetrievesItself()
    {
        $container = $this->placeholder->__invoke();
        $this->assertSame($container, $this->placeholder);
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

    public function testContainersCanBeDeleted()
    {
        $container = $this->placeholder->__invoke('foo');
        $container->set('Value');
        $this->assertTrue($this->placeholder->containerExists('foo'));
        $this->assertSame('Value', (string) $this->placeholder->__invoke('foo'));
        $this->placeholder->deleteContainer('foo');
        $this->assertFalse($this->placeholder->containerExists('foo'));
        $this->assertSame('', (string) $this->placeholder->__invoke('foo'));
    }

    public function testClearContainersRemovesAllContainers()
    {
        $this->placeholder->__invoke('foo');
        $this->placeholder->__invoke('bar');

        $this->assertTrue($this->placeholder->containerExists('foo'));
        $this->assertTrue($this->placeholder->containerExists('bar'));

        $this->placeholder->clearContainers();

        $this->assertFalse($this->placeholder->containerExists('foo'));
        $this->assertFalse($this->placeholder->containerExists('bar'));
    }

    /**
     * @return void
     */
    public function testGetContainerRetrievesTheCorrectContainer()
    {
        $container1 = $this->placeholder->__invoke('foo');
        $container2 = $this->placeholder->__invoke()->getContainer('foo');

        $this->assertSame($container1, $container2);
    }
}
