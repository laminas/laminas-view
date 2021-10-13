<?php

namespace LaminasTest\View\Helper;

use Laminas\View\Exception;
use Laminas\View\Helper\Layout;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Laminas\View\Helper\Layout
 *
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class LayoutTest extends TestCase
{
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->renderer = $renderer = new PhpRenderer();
        $this->viewModelHelper = $renderer->plugin('view_model');
        $this->helper          = $renderer->plugin('layout');

        $this->parent = new ViewModel();
        $this->parent->setTemplate('layout');
        $this->viewModelHelper->setRoot($this->parent);
    }

    public function testCallingSetTemplateAltersRootModelTemplate(): void
    {
        $this->helper->setTemplate('alternate/layout');
        $this->assertEquals('alternate/layout', $this->parent->getTemplate());
    }

    public function testCallingGetLayoutReturnsRootModelTemplate(): void
    {
        $this->assertEquals('layout', $this->helper->getLayout());
    }

    public function testCallingInvokeProxiesToSetTemplate(): void
    {
        $helper = $this->helper;
        $helper('alternate/layout');
        $this->assertEquals('alternate/layout', $this->parent->getTemplate());
    }

    public function testCallingInvokeWithNoArgumentReturnsViewModel(): void
    {
        $helper = $this->helper;
        $result = $helper();
        $this->assertSame($this->parent, $result);
    }

    public function testRaisesExceptionIfViewModelHelperHasNoRoot(): void
    {
        $renderer         = new PhpRenderer();
        $renderer->plugin('view_model');
        $helper          = $renderer->plugin('layout');

        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('view model');
        $helper->setTemplate('foo/bar');
    }
}
