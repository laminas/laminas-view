<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\View\Helper\Placeholder;
use Laminas\View\Helper\RenderToPlaceholder;
use Laminas\View\Renderer\PhpRenderer as View;
use Laminas\View\Resolver\TemplatePathStack;
use PHPUnit\Framework\TestCase;

use function assert;

/**
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class RenderToPlaceholderTest extends TestCase
{
    private View $view;
    private RenderToPlaceholder $helper;

    protected function setUp(): void
    {
        $this->view = new View();
        $resolver   = $this->view->resolver();
        assert($resolver instanceof TemplatePathStack);
        $resolver->addPath(__DIR__ . '/_files/scripts/');

        $helper = $this->view->plugin('renderToPlaceholder');
        assert($helper instanceof RenderToPlaceholder);
        $this->helper = $helper;
    }

    public function testDefaultEmpty(): void
    {
        $this->helper->__invoke('rendertoplaceholderscript.phtml', 'fooPlaceholder');
        $placeholder = $this->view->plugin('placeholder');
        assert($placeholder instanceof Placeholder);
        $this->assertEquals("Foo Bar\n", $placeholder->__invoke('fooPlaceholder')->getValue());
    }
}
