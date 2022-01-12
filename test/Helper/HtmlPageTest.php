<?php

declare(strict_types=1);

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
    /** @var HtmlPage */
    public $helper;
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
        $this->helper = new HtmlPage();
        $this->helper->setView($this->view);
    }

    public function testMakeHtmlPage(): void
    {
        $htmlPage = $this->helper->__invoke('/path/to/page.html');

        $objectStartElement = '<object data="&#x2F;path&#x2F;to&#x2F;page.html"'
                            . ' type="text&#x2F;html"'
                            . ' classid="clsid&#x3A;25336920-03F9-11CF-8FD0-00AA00686F13">';

        $this->assertStringContainsString($objectStartElement, $htmlPage);
        $this->assertStringContainsString('</object>', $htmlPage);
    }
}
