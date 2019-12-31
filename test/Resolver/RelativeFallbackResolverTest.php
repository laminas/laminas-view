<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\Resolver;

use Laminas\View\Helper\ViewModel as ViewModelHelper;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Renderer\RendererInterface;
use Laminas\View\Resolver\AggregateResolver;
use Laminas\View\Resolver\RelativeFallbackResolver;
use Laminas\View\Resolver\ResolverInterface;
use Laminas\View\Resolver\TemplateMapResolver;
use Laminas\View\Resolver\TemplatePathStack;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Laminas\View\Resolver\RelativeFallbackResolver
 */
class RelativeFallbackResolverTest extends TestCase
{
    public function testReturnsResourceFromTheSameNameSpaceWithMapResolver()
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

    public function testReturnsResourceFromTheSameNameSpaceWithPathStack()
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

    public function testReturnsResourceFromTopLevelIfExistsInsteadOfTheSameNameSpace()
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

    public function testSkipsResolutionOnViewRendererWithoutPlugins()
    {
        /* @var $baseResolver ResolverInterface|\PHPUnit_Framework_MockObject_MockObject */
        $baseResolver = $this->getMockBuilder(ResolverInterface::class)->getMock();
        $fallback     = new RelativeFallbackResolver($baseResolver);
        /* @var $renderer RendererInterface|\PHPUnit_Framework_MockObject_MockObject */
        $renderer     = $this->getMockBuilder(RendererInterface::class)->getMock();

        $baseResolver->expects($this->never())->method('resolve');

        $this->assertFalse($fallback->resolve('foo/bar', $renderer));
    }

    public function testSkipsResolutionOnViewRendererWithoutCorrectCurrentPlugin()
    {
        /* @var $baseResolver ResolverInterface|\PHPUnit_Framework_MockObject_MockObject */
        $baseResolver = $this->getMockBuilder(ResolverInterface::class)->getMock();
        $fallback     = new RelativeFallbackResolver($baseResolver);
        /* @var $renderer RendererInterface|\PHPUnit_Framework_MockObject_MockObject */
        $renderer     = $this->getMockBuilder(RendererInterface::class)
            ->setMethods(['getEngine', 'setResolver', 'plugin', 'render'])
            ->getMock();

        $baseResolver->expects($this->never())->method('resolve');
        $renderer->expects($this->once())->method('plugin')->will($this->returnValue(new stdClass()));

        $this->assertFalse($fallback->resolve('foo/bar', $renderer));
    }

    public function testSkipsResolutionOnNonExistingCurrentViewModel()
    {
        /* @var $baseResolver ResolverInterface|\PHPUnit_Framework_MockObject_MockObject */
        $baseResolver = $this->getMockBuilder(ResolverInterface::class)->getMock();
        $fallback     = new RelativeFallbackResolver($baseResolver);
        $viewModel    = new ViewModelHelper();
        /* @var $renderer RendererInterface|\PHPUnit_Framework_MockObject_MockObject */
        $renderer     = $this->getMockBuilder(RendererInterface::class)
            ->setMethods(['getEngine', 'setResolver', 'plugin', 'render'])
            ->getMock();

        $baseResolver->expects($this->never())->method('resolve');
        $renderer->expects($this->once())->method('plugin')->will($this->returnValue($viewModel));

        $this->assertFalse($fallback->resolve('foo/bar', $renderer));
    }
}
