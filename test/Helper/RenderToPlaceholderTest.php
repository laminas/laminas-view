<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\View\Helper\Placeholder;
use Laminas\View\Helper\RenderToPlaceholder;
use Laminas\View\Renderer\PhpRenderer;
use LaminasTest\View\GenerateServiceManager;
use PHPUnit\Framework\TestCase;

final class RenderToPlaceholderTest extends TestCase
{
    private RenderToPlaceholder $helper;
    private Placeholder $placeholder;

    protected function setUp(): void
    {
        $container         = GenerateServiceManager::withConfig([
            'view_manager' => [
                'template_path_stack' => [
                    __DIR__ . '/_files/scripts/',
                ],
            ],
        ]);
        $view              = $container->get(PhpRenderer::class);
        $this->placeholder = new Placeholder();
        $this->helper      = new RenderToPlaceholder($view, $this->placeholder);
    }

    public function testPlaceholderIsInitiallyEmpty(): void
    {
        self::assertSame('', $this->placeholder->__invoke('foo')->toString());
    }

    public function testPlaceholderWillContainTheContentsOfTheTemplateFile(): void
    {
        $this->helper->__invoke('rendertoplaceholderscript.phtml', 'foo');
        $this->assertSame("Foo Bar\n", $this->placeholder->__invoke('foo')->toString());
    }

    public function testContentIsAggregatedWithAppend(): void
    {
        $this->helper->__invoke('rendertoplaceholderscript.phtml', 'foo');
        $this->helper->__invoke('rendertoplaceholderscript.phtml', 'foo');
        $this->assertSame("Foo Bar\nFoo Bar\n", $this->placeholder->__invoke('foo')->toString());
    }
}
