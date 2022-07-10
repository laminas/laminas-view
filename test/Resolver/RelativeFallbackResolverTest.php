<?php

declare(strict_types=1);

namespace LaminasTest\View\Resolver;

use Laminas\View\Helper\ViewModel as ViewModelHelper;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\AggregateResolver;
use Laminas\View\Resolver\RelativeFallbackResolver;
use Laminas\View\Resolver\ResolverInterface;
use Laminas\View\Resolver\TemplateMapResolver;
use Laminas\View\Resolver\TemplatePathStack;
use PHPUnit\Framework\TestCase;
use stdClass;

use function realpath;

/**
 * @covers \Laminas\View\Resolver\RelativeFallbackResolver
 */
class RelativeFallbackResolverTest extends TestCase
{
    public function testReturnsResourceFromTheSameNameSpaceWithMapResolver(): void
    {
        $tplMapResolver = new TemplateMapResolver([
            'foo/bar' => 'foo/baz',
        ]);
        $resolver       = new RelativeFallbackResolver($tplMapResolver);
        $renderer       = new PhpRenderer();
        $view           = new ViewModel();
        $view->setTemplate('foo/zaz');
        $helper = $renderer->plugin(ViewModelHelper::class);
        $helper->setCurrent($view);

        $test = $resolver->resolve('bar', $renderer);
        $this->assertEquals('foo/baz', $test);
    }

    public function testReturnsResourceFromTheSameNameSpaceWithPathStack(): void
    {
        $pathStack = new TemplatePathStack();
        $pathStack->addPath(__DIR__ . '/../_templates');
        $resolver = new RelativeFallbackResolver($pathStack);
        $renderer = new PhpRenderer();
        $view     = new ViewModel();
        $view->setTemplate('name-space/any-view');
        $helper = $renderer->plugin(ViewModelHelper::class);
        $helper->setCurrent($view);

        $test = $resolver->resolve('bar', $renderer);
        $this->assertEquals(realpath(__DIR__ . '/../_templates/name-space/bar.phtml'), $test);
    }

    public function testReturnsResourceFromTopLevelIfExistsInsteadOfTheSameNameSpace(): void
    {
        $tplMapResolver = new TemplateMapResolver([
            'foo/bar' => 'foo/baz',
            'bar'     => 'baz',
        ]);
        $resolver       = new AggregateResolver();
        $resolver->attach($tplMapResolver);
        $resolver->attach(new RelativeFallbackResolver($tplMapResolver));
        $renderer = new PhpRenderer();
        $view     = new ViewModel();
        $view->setTemplate('foo/zaz');
        $helper = $renderer->plugin(ViewModelHelper::class);
        $helper->setCurrent($view);

        $test = $resolver->resolve('bar', $renderer);
        $this->assertEquals('baz', $test);
    }

    public function testSkipsResolutionOnViewRendererWithoutPlugins(): void
    {
        $baseResolver = $this->createMock(ResolverInterface::class);
        $baseResolver->expects(self::never())
            ->method('resolve');

        $fallback = new RelativeFallbackResolver($baseResolver);

        $renderer = $this->createMock(PhpRenderer::class);

        $this->assertFalse($fallback->resolve('foo/bar', $renderer));
    }

    public function testSkipsResolutionOnViewRendererWithoutCorrectCurrentPlugin(): void
    {
        $baseResolver = $this->createMock(ResolverInterface::class);
        $baseResolver->expects(self::never())
            ->method('resolve');

        $fallback = new RelativeFallbackResolver($baseResolver);

        $renderer = $this->createMock(PhpRenderer::class);
        $renderer->expects(self::once())
            ->method('plugin')
            ->willReturn(new stdClass());

        $this->assertFalse($fallback->resolve('foo/bar', $renderer));
    }

    public function testSkipsResolutionOnNonExistingCurrentViewModel(): void
    {
        $baseResolver = $this->createMock(ResolverInterface::class);
        $baseResolver->expects(self::never())
            ->method('resolve');

        $fallback  = new RelativeFallbackResolver($baseResolver);
        $viewModel = new ViewModelHelper();

        $renderer = $this->createMock(PhpRenderer::class);
        $renderer->expects(self::once())
            ->method('plugin')
            ->willReturn($viewModel);

        $this->assertFalse($fallback->resolve('foo/bar', $renderer));
    }
}
