<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\Helper;

use Laminas\View\Helper\HtmlFlash;
use Laminas\View\Renderer\PhpRenderer as View;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class HtmlFlashTest extends TestCase
{
    /**
     * @var HtmlFlash
     */
    public $helper;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp()
    {
        $this->view   = new View();
        $this->helper = new HtmlFlash();
        $this->helper->setView($this->view);
    }

    public function tearDown()
    {
        unset($this->helper);
    }

    public function testMakeHtmlFlash()
    {
        $htmlFlash = $this->helper->__invoke('/path/to/flash.swf');

        // @codingStandardsIgnoreStart
        $objectStartElement = '<object data="&#x2F;path&#x2F;to&#x2F;flash.swf" type="application&#x2F;x-shockwave-flash">';
        // @codingStandardsIgnoreEnd

        $this->assertContains($objectStartElement, $htmlFlash);
        $this->assertContains('</object>', $htmlFlash);
    }
}
