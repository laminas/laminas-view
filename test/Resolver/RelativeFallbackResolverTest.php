<?php

declare(strict_types=1);

namespace LaminasTest\View\Resolver;

use Laminas\View\Helper\ViewModel as ViewModelHelper;
use Laminas\View\Model\ViewModel;
use Laminas\View\Resolver\AggregateResolver;
use Laminas\View\Resolver\RelativeFallbackResolver;
use Laminas\View\Resolver\ResolverInterface;
use Laminas\View\Resolver\TemplateCannotBeFound;
use Laminas\View\Resolver\TemplateMapResolver;
use Laminas\View\Resolver\TemplatePathStack;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function realpath;

#[CoversClass(RelativeFallbackResolver::class)]
final class RelativeFallbackResolverTest extends TestCase
{
    public function testReturnsResourceFromTheSameNameSpaceWithMapResolver(): void
    {
        $helper         = new ViewModelHelper();
        $tplMapResolver = new TemplateMapResolver([
            'foo/bar' => 'foo/baz',
        ]);
        $resolver       = new RelativeFallbackResolver($tplMapResolver, $helper);
        $view           = new ViewModel();
        $view->setTemplate('foo/zaz');
        $helper->setCurrent($view);

        $test = $resolver->resolve('bar');
        $this->assertEquals('foo/baz', $test);
    }

    public function testReturnsResourceFromTheSameNameSpaceWithPathStack(): void
    {
        $view = new ViewModel();
        $view->setTemplate('name-space/any-view');
        $helper = new ViewModelHelper();
        $helper->setCurrent($view);

        $pathStack = new TemplatePathStack();
        $pathStack->addPath(__DIR__ . '/../_templates');
        $resolver = new RelativeFallbackResolver($pathStack, $helper);

        $test = $resolver->resolve('bar');
        $this->assertEquals(realpath(__DIR__ . '/../_templates/name-space/bar.phtml'), $test);
    }

    public function testReturnsResourceFromTopLevelIfExistsInsteadOfTheSameNameSpace(): void
    {
        $view = new ViewModel();
        $view->setTemplate('foo/zaz');
        $helper = new ViewModelHelper();
        $helper->setCurrent($view);

        $tplMapResolver = new TemplateMapResolver([
            'foo/bar' => 'foo/baz',
            'bar'     => 'baz',
        ]);
        $resolver       = new AggregateResolver();
        $resolver->attach($tplMapResolver);
        $resolver->attach(new RelativeFallbackResolver($tplMapResolver, $helper));

        $test = $resolver->resolve('bar');
        $this->assertEquals('baz', $test);
    }

    public function testResolutionFailsWhenTheHelperDoesNotKnowTheRuntimeCurrentModel(): void
    {
        $baseResolver = $this->createMock(ResolverInterface::class);
        $baseResolver->expects(self::never())
            ->method('resolve');

        $fallback = new RelativeFallbackResolver($baseResolver, new ViewModelHelper());

        $this->expectException(TemplateCannotBeFound::class);
        $fallback->resolve('foo/bar');
    }

    public function testResolutionFailsWhenTheComposedResolverFails(): void
    {
        $view = new ViewModel();
        $view->setTemplate('name-space/any-view');
        $helper = new ViewModelHelper();
        $helper->setCurrent($view);

        $pathStack = new TemplatePathStack();
        $pathStack->addPath(__DIR__ . '/../_templates');
        $resolver = new RelativeFallbackResolver($pathStack, $helper);

        // The foo.phtml file should not exist in ../_templates/name-space/
        $this->expectException(TemplateCannotBeFound::class);
        $resolver->resolve('foo');
    }

    public function testResolutionFailsWhenTheCurrentTemplateHasZeroDepth(): void
    {
        $view = new ViewModel();
        $view->setTemplate('empty'); // Known template in ../_templates/
        $helper = new ViewModelHelper();
        $helper->setCurrent($view);

        $pathStack = new TemplatePathStack();
        $pathStack->addPath(__DIR__ . '/../_templates');
        $resolver = new RelativeFallbackResolver($pathStack, $helper);

        $this->expectException(TemplateCannotBeFound::class);
        $resolver->resolve('test'); // Can actually be found in ../_templates/test.phtml
    }
}
