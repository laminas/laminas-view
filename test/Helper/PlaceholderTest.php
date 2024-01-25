<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\View\Helper\Placeholder;
use Laminas\View\Helper\Placeholder\Container\AbstractContainer;
use Laminas\View\Renderer\PhpRenderer as View;
use PHPUnit\Framework\TestCase;

class PlaceholderTest extends TestCase
{
    public Placeholder $placeholder;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->placeholder = new Placeholder();
    }

    public function testSetView(): void
    {
        $view = new View();
        $this->placeholder->setView($view);
        $this->assertSame($view, $this->placeholder->getView());
    }

    public function testContainerExists(): void
    {
        $this->placeholder->__invoke('foo');
        $containerExists = $this->placeholder->__invoke()->containerExists('foo');

        $this->assertTrue($containerExists);
    }

    public function testPlaceholderRetrievesContainer(): void
    {
        $container = $this->placeholder->__invoke('foo');
        $this->assertInstanceOf(AbstractContainer::class, $container);
    }

    public function testPlaceholderRetrievesItself(): void
    {
        $container = $this->placeholder->__invoke();
        $this->assertSame($container, $this->placeholder);
    }

    public function testPlaceholderRetrievesSameContainerOnSubsequentCalls(): void
    {
        $container1 = $this->placeholder->__invoke('foo');
        $container2 = $this->placeholder->__invoke('foo');
        $this->assertSame($container1, $container2);
    }

    public function testContainersCanBeDeleted(): void
    {
        $container = $this->placeholder->__invoke('foo');
        $container->set('Value');
        $this->assertTrue($this->placeholder->containerExists('foo'));
        $this->assertSame('Value', (string) $this->placeholder->__invoke('foo'));
        $this->placeholder->deleteContainer('foo');
        $this->assertFalse($this->placeholder->containerExists('foo'));
        $this->assertSame('', (string) $this->placeholder->__invoke('foo'));
    }

    public function testClearContainersRemovesAllContainers(): void
    {
        $this->placeholder->__invoke('foo');
        $this->placeholder->__invoke('bar');

        $this->assertTrue($this->placeholder->containerExists('foo'));
        $this->assertTrue($this->placeholder->containerExists('bar'));

        $this->placeholder->clearContainers();

        $this->assertFalse($this->placeholder->containerExists('foo'));
        $this->assertFalse($this->placeholder->containerExists('bar'));
    }

    public function testGetContainerRetrievesTheCorrectContainer(): void
    {
        $container1 = $this->placeholder->__invoke('foo');
        $container2 = $this->placeholder->__invoke()->getContainer('foo');

        $this->assertSame($container1, $container2);
    }
}
