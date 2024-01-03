<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\Placeholder;

use Laminas\View\Exception\DomainException;
use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Helper\Placeholder\Container;
use Laminas\View\Helper\Placeholder\Container\AbstractContainer;
use Laminas\View\Renderer\PhpRenderer as View;
use LaminasTest\View\Helper\TestAsset\Bar;
use LaminasTest\View\Helper\TestAsset\Foo;
use PHPUnit\Framework\TestCase;

class StandaloneContainerTest extends TestCase
{
    private Foo $helper;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->helper = new Foo();
    }

    public function testSetContainer(): void
    {
        /** @var Container<array-key, mixed> $container */
        $container = new Container();
        $this->assertNotSame($container, $this->helper->getContainer());
        $this->helper->setContainer($container);
        $this->assertSame($container, $this->helper->getContainer());
    }

    public function testGetContainer(): void
    {
        $container = $this->helper->getContainer();
        $this->assertInstanceOf(AbstractContainer::class, $container);
    }

    public function testGetContainerCreatesNewContainer(): void
    {
        $this->helper->deleteContainer();
        $container = $this->helper->getContainer();
        $this->assertInstanceOf(AbstractContainer::class, $container);
    }

    public function testDeleteContainer(): void
    {
        $this->assertNotNull($this->helper->getContainer());
        $this->assertTrue($this->helper->deleteContainer());
        $this->assertFalse($this->helper->deleteContainer());
    }

    public function testSetContainerClassThrowsDomainException(): void
    {
        $this->expectException(DomainException::class);
        /** @psalm-suppress UndefinedClass, ArgumentTypeCoercion */
        $this->helper->setContainerClass('not-a-known-class');
    }

    public function testSetContainerClassThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        /** @psalm-suppress InvalidArgument */
        $this->helper->setContainerClass(static::class);
    }

    public function testSetGetContainerClass(): void
    {
        $this->helper->setContainerClass(Bar::class);
        $this->assertEquals(Bar::class, $this->helper->getContainerClass());
    }

    public function testViewAccessorWorks(): void
    {
        $view = new View();
        $this->helper->setView($view);
        $this->assertSame($view, $this->helper->getView());
    }

    public function testContainerDoesNotPersistBetweenInstances(): void
    {
        $foo1 = new Foo();
        $foo1->getContainer()->append('Foo');
        $foo1->setSeparator(' - ');

        $foo2 = new Foo();
        $foo2->getContainer()->append('Bar');

        $test = $foo2->toString();
        $this->assertStringNotContainsString('Foo', $test);
        $this->assertStringNotContainsString(' - ', $test);
        $this->assertStringContainsString('Bar', $test);
    }
}
