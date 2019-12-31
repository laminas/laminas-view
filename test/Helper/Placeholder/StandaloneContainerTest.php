<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\Helper\Placeholder;

use Laminas\View\Renderer\PhpRenderer as View;

/**
 * Test class for Laminas_View_Helper_Placeholder_StandaloneContainer.
 *
 * @category   Laminas
 * @package    Laminas_View
 * @subpackage UnitTests
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class StandaloneContainerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
        \Laminas\View\Helper\Placeholder\Registry::unsetRegistry();
        $this->basePath = __DIR__ . '/_files/modules';
        $this->helper = new Foo();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->helper);
    }

    public function testViewAccessorWorks()
    {
        $view = new View();
        $this->helper->setView($view);
        $this->assertSame($view, $this->helper->getView());
    }

    public function testContainersPersistBetweenInstances()
    {
        $foo1 = new Foo;
        $foo1->append('Foo');
        $foo1->setSeparator(' - ');

        $foo2 = new Foo;
        $foo2->append('Bar');

        $test = $foo1->toString();
        $this->assertContains('Foo', $test);
        $this->assertContains(' - ', $test);
        $this->assertContains('Bar', $test);
    }
}

class Foo extends \Laminas\View\Helper\Placeholder\Container\AbstractStandalone
{
    protected $_regKey = 'foo';
    public function direct() {}
}
