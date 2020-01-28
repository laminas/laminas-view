<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\Helper;

use Laminas\View\Renderer\PhpRenderer as View;
use Laminas\View\Helper\HtmlQuicktime;

/**
 * @category   Laminas
 * @package    Laminas_View
 * @subpackage UnitTests
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class HtmlQuicktimeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Laminas_View_Helper_HtmlQuicktime
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
        $this->helper = new HtmlQuicktime();
        $this->helper->setView($this->view);
    }

    public function tearDown()
    {
        unset($this->helper);
    }

    public function testMakeHtmlQuicktime()
    {
        $htmlQuicktime = $this->helper->__invoke('/path/to/quicktime.mov');

        $objectStartElement = '<object data="/path/to/quicktime.mov"'
                            . ' type="video/quicktime"'
                            . ' classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B"'
                            . ' codebase="http://www.apple.com/qtactivex/qtplugin.cab">';

        $this->assertContains($objectStartElement, $htmlQuicktime);
        $this->assertContains('</object>', $htmlQuicktime);
    }
}
