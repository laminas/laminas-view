<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\View\Helper\Doctype;
use Laminas\View\Helper\HtmlObject;
use Laminas\View\Renderer\PhpRenderer as View;
use Laminas\View\Renderer\RendererInterface;
use PHPUnit\Framework\TestCase;

class HtmlObjectTest extends TestCase
{
    private HtmlObject $helper;
    private View $view;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp(): void
    {
        $this->view   = new View();
        $this->helper = new HtmlObject();
        $this->helper->setView($this->view);
    }

    public function testViewObjectIsSet(): void
    {
        $this->assertInstanceof(RendererInterface::class, $this->helper->getView());
    }

    public function testMakeHtmlObjectWithoutAttribsWithoutParams(): void
    {
        $htmlObject = $this->helper->__invoke('datastring', 'typestring');

        $this->assertStringContainsString('<object data="datastring" type="typestring">', $htmlObject);
        $this->assertStringContainsString('</object>', $htmlObject);
    }

    public function testMakeHtmlObjectWithAttribsWithoutParams(): void
    {
        $attribs = [
            'attribkey1' => 'attribvalue1',
            'attribkey2' => 'attribvalue2',
        ];

        $htmlObject = $this->helper->__invoke('datastring', 'typestring', $attribs);

        $this->assertStringContainsString(
            '<object data="datastring" type="typestring" attribkey1="attribvalue1" attribkey2="attribvalue2">',
            $htmlObject
        );
        $this->assertStringContainsString('</object>', $htmlObject);
    }

    public function testMakeHtmlObjectWithoutAttribsWithParamsHtml(): void
    {
        $this->view->plugin(Doctype::class)->__invoke(Doctype::HTML4_STRICT);

        $params = [
            'paramname1' => 'paramvalue1',
            'paramname2' => 'paramvalue2',
        ];

        $htmlObject = $this->helper->__invoke('datastring', 'typestring', [], $params);

        $this->assertStringContainsString('<object data="datastring" type="typestring">', $htmlObject);
        $this->assertStringContainsString('</object>', $htmlObject);

        foreach ($params as $key => $value) {
            $param = '<param name="' . $key . '" value="' . $value . '">';

            $this->assertStringContainsString($param, $htmlObject);
        }
    }

    public function testMakeHtmlObjectWithoutAttribsWithParamsXhtml(): void
    {
        $this->view->plugin(Doctype::class)->__invoke(Doctype::XHTML1_STRICT);

        $params = [
            'paramname1' => 'paramvalue1',
            'paramname2' => 'paramvalue2',
        ];

        $htmlObject = $this->helper->__invoke('datastring', 'typestring', [], $params);

        $this->assertStringContainsString('<object data="datastring" type="typestring">', $htmlObject);
        $this->assertStringContainsString('</object>', $htmlObject);

        foreach ($params as $key => $value) {
            $param = '<param name="' . $key . '" value="' . $value . '" />';

            $this->assertStringContainsString($param, $htmlObject);
        }
    }

    public function testMakeHtmlObjectWithContent(): void
    {
        $htmlObject = $this->helper->__invoke('datastring', 'typestring', [], [], 'testcontent');

        $this->assertStringContainsString('<object data="datastring" type="typestring">', $htmlObject);
        $this->assertStringContainsString('testcontent', $htmlObject);
        $this->assertStringContainsString('</object>', $htmlObject);
    }
}
