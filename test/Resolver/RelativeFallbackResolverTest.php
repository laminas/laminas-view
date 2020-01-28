<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\Resolver;

use PHPUnit_Framework_TestCase as TestCase;
use stdClass;
use Laminas\View\Helper\ViewModel as ViewModelHelper;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\RelativeFallbackResolver;
use Laminas\View\Resolver\TemplateMapResolver;
use Laminas\View\Resolver\TemplatePathStack;
use Laminas\View\Resolver\AggregateResolver;

/**
 * @covers \Laminas\View\Resolver\RelativeFallbackResolver
 */
class RelativeFallbackResolverTest extends TestCase
{
    public function testReturnsResourceFromTheSameNameSpaceWithMapResolver()
    {
        $tplMapResolver = new TemplateMapResolver(array(
            'foo/bar' => 'foo/baz',
        ));
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
        $tplMapResolver = new TemplateMapResolver(array(
            'foo/bar' => 'foo/baz',
            'bar' => 'baz',
        ));
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
        /* @var $baseResolver \Laminas\View\Resolver\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject */
        $baseResolver = $this->getMock('Laminas\View\Resolver\ResolverInterface');
        $fallback     = new RelativeFallbackResolver($baseResolver);
        /* @var $renderer \Laminas\View\Renderer\RendererInterface|\PHPUnit_Framework_MockObject_MockObject */
        $renderer     = $this->getMock('Laminas\View\Renderer\RendererInterface');

        $baseResolver->expects($this->never())->method('resolve');

        $this->assertFalse($fallback->resolve('foo/bar', $renderer));
    }

    public function testSkipsResolutionOnViewRendererWithoutCorrectCurrentPlugin()
    {
        /* @var $baseResolver \Laminas\View\Resolver\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject */
        $baseResolver = $this->getMock('Laminas\View\Resolver\ResolverInterface');
        $fallback     = new RelativeFallbackResolver($baseResolver);
        /* @var $renderer \Laminas\View\Renderer\RendererInterface|\PHPUnit_Framework_MockObject_MockObject */
        $renderer     = $this->getMock(
            'Laminas\View\Renderer\RendererInterface',
            array('getEngine', 'setResolver', 'plugin', 'render')
        );

        $baseResolver->expects($this->never())->method('resolve');
        $renderer->expects($this->once())->method('plugin')->will($this->returnValue(new stdClass()));

        $this->assertFalse($fallback->resolve('foo/bar', $renderer));
    }

    public function testSkipsResolutionOnNonExistingCurrentViewModel()
    {
        /* @var $baseResolver \Laminas\View\Resolver\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject */
        $baseResolver = $this->getMock('Laminas\View\Resolver\ResolverInterface');
        $fallback     = new RelativeFallbackResolver($baseResolver);
        $viewModel    = new ViewModelHelper();
        /* @var $renderer \Laminas\View\Renderer\RendererInterface|\PHPUnit_Framework_MockObject_MockObject */
        $renderer     = $this->getMock(
            'Laminas\View\Renderer\RendererInterface',
            array('getEngine', 'setResolver', 'plugin', 'render')
        );

        $baseResolver->expects($this->never())->method('resolve');
        $renderer->expects($this->once())->method('plugin')->will($this->returnValue($viewModel));

        $this->assertFalse($fallback->resolve('foo/bar', $renderer));
    }
}
