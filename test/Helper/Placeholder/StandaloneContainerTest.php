<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\Helper\Placeholder;

use Laminas\View\Helper\Placeholder\Container;
use Laminas\View\Renderer\PhpRenderer as View;

/**
 * Test class for Laminas\View\Helper\Placeholder\Container.
 *
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class StandaloneContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Foo
     */
    protected $helper;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
        $this->helper = new Foo();
    }

    /**
     * @return void
     */
    public function testSetContainer()
    {
        $container = new Container();
        $this->assertNotSame($container, $this->helper->getContainer());
        $this->helper->setContainer($container);
        $this->assertSame($container, $this->helper->getContainer());
    }

    /**
     * @return void
     */
    public function testGetContainer()
    {
        $container = $this->helper->getContainer();
        $this->assertInstanceOf('Laminas\View\Helper\Placeholder\Container', $container);
    }

    /**
     * @return void
     */
    public function testGetContainerCreatesNewContainer()
    {
        $this->helper->deleteContainer();
        $container = $this->helper->getContainer();
        $this->assertInstanceOf('Laminas\View\Helper\Placeholder\Container', $container);
    }

    /**
     * @return void
     */
    public function testDeleteContainer()
    {
        $this->assertNotNull($this->helper->getContainer());
        $this->assertTrue($this->helper->deleteContainer());
        $this->assertFalse($this->helper->deleteContainer());
    }

    /**
     * @expectedException DomainException
     * @return void
     */
    public function testSetContainerClassThrowsDomainException()
    {
        $this->helper->setContainerClass('bat');
    }

    /**
     * @expectedException InvalidArgumentException
     * @return void
     */
    public function testSetContainerClassThrowsInvalidArgumentException()
    {
        $this->helper->setContainerClass(get_class($this));
    }

    /**
     * @return void
     */
    public function testSetGetContainerClass()
    {
        $this->helper->setContainerClass('LaminasTest\View\Helper\Placeholder\Bar');
        $this->assertEquals('LaminasTest\View\Helper\Placeholder\Bar', $this->helper->getContainerClass());
    }

    /**
     * @return void
     */
    public function testViewAccessorWorks()
    {
        $view = new View();
        $this->helper->setView($view);
        $this->assertSame($view, $this->helper->getView());
    }

    /**
     * @return void
     */
    public function testContainerDoesNotPersistBetweenInstances()
    {
        $foo1 = new Foo;
        $foo1->append('Foo');
        $foo1->setSeparator(' - ');

        $foo2 = new Foo;
        $foo2->append('Bar');

        $test = $foo2->toString();
        $this->assertNotContains('Foo', $test);
        $this->assertNotContains(' - ', $test);
        $this->assertContains('Bar', $test);
    }
}

class Foo extends \Laminas\View\Helper\Placeholder\Container\AbstractStandalone
{
    protected $_regKey = 'foo';
    public function direct()
    {
    }
}

class Bar extends \Laminas\View\Helper\Placeholder\Container\AbstractContainer
{
}
