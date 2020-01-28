<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\Helper;

use Laminas\View\Renderer\PhpRenderer as View;
use Laminas\View\Helper\Doctype;
use Laminas\View\Helper\HtmlObject;

/**
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class HtmlObjectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HtmlObject
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
        $this->helper = new HtmlObject();
        $this->helper->setView($this->view);
    }

    public function tearDown()
    {
        unset($this->helper);
    }

    public function testViewObjectIsSet()
    {
        $this->assertInstanceof('Laminas\View\Renderer\RendererInterface', $this->helper->getView());
    }

    public function testMakeHtmlObjectWithoutAttribsWithoutParams()
    {
        $htmlObject = $this->helper->__invoke('datastring', 'typestring');

        $this->assertContains('<object data="datastring" type="typestring">', $htmlObject);
        $this->assertContains('</object>', $htmlObject);
    }

    public function testMakeHtmlObjectWithAttribsWithoutParams()
    {
        $attribs = array('attribkey1' => 'attribvalue1',
                         'attribkey2' => 'attribvalue2');

        $htmlObject = $this->helper->__invoke('datastring', 'typestring', $attribs);

        $this->assertContains('<object data="datastring" type="typestring" attribkey1="attribvalue1" attribkey2="attribvalue2">', $htmlObject);
        $this->assertContains('</object>', $htmlObject);
    }

    public function testMakeHtmlObjectWithoutAttribsWithParamsHtml()
    {
        $this->view->plugin('doctype')->__invoke(Doctype::HTML4_STRICT);

        $params = array('paramname1' => 'paramvalue1',
                        'paramname2' => 'paramvalue2');

        $htmlObject = $this->helper->__invoke('datastring', 'typestring', array(), $params);

        $this->assertContains('<object data="datastring" type="typestring">', $htmlObject);
        $this->assertContains('</object>', $htmlObject);

        foreach ($params as $key => $value) {
            $param = '<param name="' . $key . '" value="' . $value . '">';

            $this->assertContains($param, $htmlObject);
        }
    }

    public function testMakeHtmlObjectWithoutAttribsWithParamsXhtml()
    {
        $this->view->plugin('doctype')->__invoke(Doctype::XHTML1_STRICT);

        $params = array('paramname1' => 'paramvalue1',
                        'paramname2' => 'paramvalue2');

        $htmlObject = $this->helper->__invoke('datastring', 'typestring', array(), $params);

        $this->assertContains('<object data="datastring" type="typestring">', $htmlObject);
        $this->assertContains('</object>', $htmlObject);

        foreach ($params as $key => $value) {
            $param = '<param name="' . $key . '" value="' . $value . '" />';

            $this->assertContains($param, $htmlObject);
        }
    }

    public function testMakeHtmlObjectWithContent()
    {
        $htmlObject = $this->helper->__invoke('datastring', 'typestring', array(), array(), 'testcontent');

        $this->assertContains('<object data="datastring" type="typestring">', $htmlObject);
        $this->assertContains('testcontent', $htmlObject);
        $this->assertContains('</object>', $htmlObject);
    }
}
