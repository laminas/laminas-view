<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\Helper\Placeholder;

use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Helper\Placeholder\Container;
use Laminas\View\Helper\Placeholder\Registry;
use LaminasTest\View\Helper\TestAsset;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Laminas\View\Helper\Placeholder\Registry.
 *
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class RegistryTest extends TestCase
{
    /**
     * @var Registry
     */
    public $registry;


    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->registry = new Registry();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->registry);
    }

    /**
     * @return void
     */
    public function testCreateContainer()
    {
        $this->assertFalse($this->registry->containerExists('foo'));
        $this->registry->createContainer('foo');
        $this->assertTrue($this->registry->containerExists('foo'));
    }

    /**
     * @return void
     */
    public function testCreateContainerCreatesDefaultContainerClass()
    {
        $this->assertFalse($this->registry->containerExists('foo'));
        $container = $this->registry->createContainer('foo');
        $this->assertInstanceOf(Container::class, $container);
    }

    /**
     * @return void
     */
    public function testGetContainerCreatesContainerIfNonExistent()
    {
        $this->assertFalse($this->registry->containerExists('foo'));
        $container = $this->registry->getContainer('foo');
        $this->assertInstanceOf(Container\AbstractContainer::class, $container);
        $this->assertTrue($this->registry->containerExists('foo'));
    }

    /**
     * @return void
     */
    public function testSetContainerCreatesRegistryEntry()
    {
        $foo = new Container(['foo', 'bar']);
        $this->assertFalse($this->registry->containerExists('foo'));
        $this->registry->setContainer('foo', $foo);
        $this->assertTrue($this->registry->containerExists('foo'));
    }

    public function testSetContainerCreatesRegistersContainerInstance(): void
    {
        $foo = new Container(['foo', 'bar']);
        $this->assertFalse($this->registry->containerExists('foo'));
        $this->registry->setContainer('foo', $foo);
        $container = $this->registry->getContainer('foo');
        $this->assertSame($foo, $container);
    }

    public function testContainerClassAccessorsSetState(): void
    {
        $this->assertEquals(Container::class, $this->registry->getContainerClass());
        $this->registry->setContainerClass(TestAsset\MockContainer::class);
        $this->assertEquals(
            TestAsset\MockContainer::class,
            $this->registry->getContainerClass()
        );
    }

    public function testSetContainerClassThrowsExceptionWithInvalidContainerClass(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid Container class specified');
        $this->registry->setContainerClass(TestAsset\BogusContainer::class);
    }

    public function testDeletingContainerRemovesFromRegistry(): void
    {
        $this->registry->createContainer('foo');
        $this->assertTrue($this->registry->containerExists('foo'));
        $result = $this->registry->deleteContainer('foo');
        $this->assertFalse($this->registry->containerExists('foo'));
        $this->assertTrue($result);
    }

    public function testDeleteContainerReturnsFalseIfContainerDoesNotExist(): void
    {
        $result = $this->registry->deleteContainer('foo');
        $this->assertFalse($result);
    }

    public function testUsingCustomContainerClassCreatesContainersOfCustomClass(): void
    {
        $this->registry->setContainerClass(TestAsset\MockContainer::class);
        $container = $this->registry->createContainer('foo');
        $this->assertInstanceOf(TestAsset\MockContainer::class, $container);
    }

    /**
     * @group Laminas-10793
     *
     * @return void
     */
    public function testSetValueCreateContainer(): void
    {
        $this->registry->setContainerClass(TestAsset\MockContainer::class);
        $data = [
            'Laminas-10793'
        ];
        $container = $this->registry->createContainer('foo', $data);
        $this->assertEquals(['Laminas-10793'], $container->data);
    }
}
