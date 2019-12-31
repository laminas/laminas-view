<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\View\Helper;

use Zend\View\Helper;

/**
 * Test class for Zend\View\Helper\InlineScript.
 *
 * @group      Zend_View
 * @group      Zend_View_Helper
 */
class InlineScriptTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Helper\InlineScript
     */
    public $helper;

    /**
     * @var string
     */
    public $basePath;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
        $this->basePath = __DIR__ . '/_files/modules';
        $this->helper = new Helper\InlineScript();
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

    public function testInlineScriptReturnsObjectInstance()
    {
        $placeholder = $this->helper->__invoke();
        $this->assertInstanceOf('Zend\View\Helper\InlineScript', $placeholder);
    }
}
