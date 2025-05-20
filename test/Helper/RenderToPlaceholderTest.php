<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\ServiceManager\ServiceManager;
use Laminas\View\ConfigProvider;
use Laminas\View\Helper\Placeholder;
use Laminas\View\Helper\RenderToPlaceholder;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplatePathStack;
use PHPUnit\Framework\TestCase;

use function array_merge_recursive;

final class RenderToPlaceholderTest extends TestCase
{
    private RenderToPlaceholder $helper;
    private Placeholder $placeholder;

    protected function setUp(): void
    {
        $config                             = array_merge_recursive(
            (new ConfigProvider())->__invoke(),
            [
                'view_manager' => [
                    'template_path_stack' => [
                        __DIR__ . '/_files/scripts/',
                    ],
                ],
            ],
        );
        $config['dependencies']['services'] = ['config' => $config];
        $serviceManager                     = new ServiceManager($config['dependencies']);

        $view = $serviceManager->get(PhpRenderer::class);
        $view->setResolver($serviceManager->get(TemplatePathStack::class));

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
