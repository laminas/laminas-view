<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\View\Exception;
use Laminas\View\Helper\Layout;
use Laminas\View\Helper\ViewModel as ViewModelHelper;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use PHPUnit\Framework\TestCase;

use function assert;

/**
 * Test class for Laminas\View\Helper\Layout
 *
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class LayoutTest extends TestCase
{
    /** @var Layout */
    private $helper;
    /** @var ViewModel */
    private $parent;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $renderer        = new PhpRenderer();
        $viewModelHelper = $renderer->plugin('view_model');
        assert($viewModelHelper instanceof ViewModelHelper);
        $helper = $renderer->plugin('layout');
        assert($helper instanceof Layout);

        $this->helper = $helper;
        $this->parent = new ViewModel();
        $this->parent->setTemplate('layout');
        $viewModelHelper->setRoot($this->parent);
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
        $renderer = new PhpRenderer();
        $renderer->plugin('view_model');
        $helper = $renderer->plugin('layout');

        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('view model');
        $helper->setTemplate('foo/bar');
    }
}
