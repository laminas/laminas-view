<?php

namespace LaminasTest\View\Helper\Placeholder;

use Laminas\View\Exception\DomainException;
use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Helper\Placeholder\Container;
use Laminas\View\Renderer\PhpRenderer as View;
use LaminasTest\View\Helper\TestAsset\Foo;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Laminas\View\Helper\Placeholder\Container.
 *
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class StandaloneContainerTest extends TestCase
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
    protected function setUp(): void
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
        $this->assertInstanceOf(Container::class, $container);
    }

    /**
     * @return void
     */
    public function testGetContainerCreatesNewContainer()
    {
        $this->helper->deleteContainer();
        $container = $this->helper->getContainer();
        $this->assertInstanceOf(Container::class, $container);
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
     * @return void
     */
    public function testSetContainerClassThrowsDomainException()
    {
        $this->expectException(DomainException::class);
        $this->helper->setContainerClass('bat');
    }

    /**
     * @return void
     */
    public function testSetContainerClassThrowsInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->helper->setContainerClass(get_class($this));
    }

    /**
     * @return void
     */
    public function testSetGetContainerClass()
    {
        $this->helper->setContainerClass('LaminasTest\View\Helper\TestAsset\Bar');
        $this->assertEquals('LaminasTest\View\Helper\TestAsset\Bar', $this->helper->getContainerClass());
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
        $this->assertStringNotContainsString('Foo', $test);
        $this->assertStringNotContainsString(' - ', $test);
        $this->assertStringContainsString('Bar', $test);
    }
}
