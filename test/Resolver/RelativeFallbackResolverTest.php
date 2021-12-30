<?php

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
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use stdClass;

use function realpath;

/**
 * @covers \Laminas\View\Resolver\RelativeFallbackResolver
 */
class RelativeFallbackResolverTest extends TestCase
{
    use ProphecyTrait;

    public function testReturnsResourceFromTheSameNameSpaceWithMapResolver(): void
    {
        $tplMapResolver = new TemplateMapResolver([
            'foo/bar' => 'foo/baz',
        ]);
        $resolver = new RelativeFallbackResolver($tplMapResolver);
        $renderer = new PhpRenderer();
        $view = new ViewModel();
        $view->setTemplate('foo/zaz');
        $helper = $renderer->plugin('view_model');
        /* @var $helper ViewModelHelper */
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
        $view = new ViewModel();
        $view->setTemplate('name-space/any-view');
        /* @var $helper ViewModelHelper */
        $helper = $renderer->plugin('view_model');
        $helper->setCurrent($view);

        $test = $resolver->resolve('bar', $renderer);
        $this->assertEquals(realpath(__DIR__ . '/../_templates/name-space/bar.phtml'), $test);
    }

    public function testReturnsResourceFromTopLevelIfExistsInsteadOfTheSameNameSpace(): void
    {
        $tplMapResolver = new TemplateMapResolver([
            'foo/bar' => 'foo/baz',
            'bar' => 'baz',
        ]);
        $resolver = new AggregateResolver();
        $resolver->attach($tplMapResolver);
        $resolver->attach(new RelativeFallbackResolver($tplMapResolver));
        $renderer = new PhpRenderer();
        $view = new ViewModel();
        $view->setTemplate('foo/zaz');
        $helper = $renderer->plugin('view_model');
        /* @var $helper ViewModelHelper */
        $helper->setCurrent($view);

        $test = $resolver->resolve('bar', $renderer);
        $this->assertEquals('baz', $test);
    }

    public function testSkipsResolutionOnViewRendererWithoutPlugins(): void
    {
        $baseResolver = $this->prophesize(ResolverInterface::class);
        $baseResolver->resolve()->shouldNotBeCalled();
        $fallback = new RelativeFallbackResolver($baseResolver->reveal());

        $renderer = $this->prophesize(PhpRenderer::class)->reveal();

        $this->assertFalse($fallback->resolve('foo/bar', $renderer));
    }

    public function testSkipsResolutionOnViewRendererWithoutCorrectCurrentPlugin(): void
    {
        $baseResolver = $this->prophesize(ResolverInterface::class);
        $baseResolver->resolve()->shouldNotBeCalled();

        $fallback = new RelativeFallbackResolver($baseResolver->reveal());

        $renderer = $this->prophesize(PhpRenderer::class);
        $renderer->plugin(Argument::any())->willReturn(new stdClass())->shouldBeCalledTimes(1);

        $this->assertFalse($fallback->resolve('foo/bar', $renderer->reveal()));
    }

    public function testSkipsResolutionOnNonExistingCurrentViewModel(): void
    {
        $baseResolver = $this->prophesize(ResolverInterface::class);
        $baseResolver->resolve()->shouldNotBeCalled();

        $fallback  = new RelativeFallbackResolver($baseResolver->reveal());
        $viewModel = new ViewModelHelper();

        $renderer = $this->prophesize(PhpRenderer::class);
        $renderer->plugin(Argument::any())->willReturn($viewModel)->shouldBeCalledTimes(1);

        $this->assertFalse($fallback->resolve('foo/bar', $renderer->reveal()));
    }
}
