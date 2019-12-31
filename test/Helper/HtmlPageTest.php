<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\Helper;

use Laminas\View\Helper\HtmlPage;
use Laminas\View\Renderer\PhpRenderer as View;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class HtmlPageTest extends TestCase
{
    /**
     * @var HtmlPage
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
        $this->helper = new HtmlPage();
        $this->helper->setView($this->view);
    }

    protected function tearDown()
    {
        unset($this->helper);
    }

    public function testMakeHtmlPage()
    {
        $htmlPage = $this->helper->__invoke('/path/to/page.html');

        $objectStartElement = '<object data="&#x2F;path&#x2F;to&#x2F;page.html"'
                            . ' type="text&#x2F;html"'
                            . ' classid="clsid&#x3A;25336920-03F9-11CF-8FD0-00AA00686F13">';

        $this->assertContains($objectStartElement, $htmlPage);
        $this->assertContains('</object>', $htmlPage);
    }
}
