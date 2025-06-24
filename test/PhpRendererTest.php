<?php

declare(strict_types=1);

namespace LaminasTest\View;

use ArrayObject;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Exception\DomainException;
use Laminas\View\Exception\RuntimeException;
use Laminas\View\Exception\UnexpectedValueException;
use Laminas\View\Helper\Doctype;
use Laminas\View\Helper\ViewModel as ViewModelHelper;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\AggregateResolver;
use Laminas\View\Resolver\TemplateMapResolver;
use Laminas\View\Resolver\TemplatePathStack;
use Laminas\View\Variables;
use LaminasTest\View\TestAsset\Invokable;
use LaminasTest\View\TestAsset\SharedInstance;
use LaminasTest\View\TestAsset\Uninvokable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Throwable;

use function realpath;
use function restore_error_handler;
use function set_error_handler;

use const E_WARNING;
use const PHP_EOL;

final class PhpRendererTest extends TestCase
{
    private PhpRenderer $renderer;
    private ServiceManager $container;

    protected function setUp(): void
    {
        $this->container = GenerateServiceManager::withConfig([
            'view_helpers' => [
                'services'  => [
                    'uninvokable' => new Uninvokable(),
                    'invokable'   => new Invokable(),
                ],
                'factories' => [
                    'sharedInstance'    => fn () => new SharedInstance(),
                    'nonSharedInstance' => fn () => new SharedInstance(),
                ],
                'shared'    => [
                    'sharedInstance'    => true,
                    'nonSharedInstance' => false,
                ],
            ],
        ]);

        $this->renderer = $this->container->get(PhpRenderer::class);
    }

    public function testUsesAggregateResolverAsDefaultResolver(): void
    {
        $this->assertInstanceOf(AggregateResolver::class, $this->renderer->resolver());
    }

    private function resolver(): TemplatePathStack
    {
        return $this->container->get(TemplatePathStack::class);
    }

    public function testPassingNameToResolverReturnsScriptName(): void
    {
        $this->resolver()->addPath(__DIR__ . '/_templates');
        $filename = $this->renderer->resolver('test.phtml');
        $this->assertEquals(realpath(__DIR__ . '/_templates/test.phtml'), $filename);
    }

    public function testUsesVariablesObjectForVarsByDefault(): void
    {
        $this->assertInstanceOf(Variables::class, $this->renderer->vars());
    }

    public function testCanSpecifyArrayAccessForVars(): void
    {
        $a = new ArrayObject(['baz' => 'bat']);
        $this->renderer->setVars($a);
        $this->assertSame($a->getArrayCopy(), $this->renderer->vars()->getArrayCopy());
    }

    public function testCanSpecifyArrayForVars(): void
    {
        $vars = ['foo' => 'bar'];
        $this->renderer->setVars($vars);
        $this->assertEquals($vars, $this->renderer->vars()->getArrayCopy());
    }

    public function testPassingArgumentToVarsReturnsValueFromThatKey(): void
    {
        $this->renderer->vars()->assign(['foo' => 'bar']);
        $this->assertEquals('bar', $this->renderer->vars('foo'));
    }

    public function testPassingArgumentToPluginReturnsHelperByThatName(): void
    {
        $helper = $this->renderer->plugin('doctype');
        $this->assertInstanceOf(Doctype::class, $helper);
    }

    public function testFilterCanBeAddedToMutateOutput(): void
    {
        $this->resolver()->addPath(__DIR__ . '/_templates');
        $output = $this->renderer->render('empty.phtml');
        self::assertSame('Empty view' . PHP_EOL, $output);

        $filter = static fn (string $content): string => $content . 'foo';
        $this->renderer->setFilter($filter);
        $output = $this->renderer->render('empty.phtml');
        self::assertSame('Empty view' . PHP_EOL . 'foo', $output);
    }

    public function testRenderingAllowsVariableSubstitutions(): void
    {
        $expected = 'foo INJECT baz';
        $this->renderer->vars()->assign(['bar' => 'INJECT']);
        $this->resolver()->addPath(__DIR__ . '/_templates');
        $test = $this->renderer->render('test.phtml');
        $this->assertStringContainsString($expected, $test);
    }

    public function testRenderingFiltersContentWithFilterChain(): void
    {
        $filter   = static fn (string $content): string => $content . 'Miss Piggy';
        $this->renderer->setFilter($filter);
        $expected = 'Empty view' . PHP_EOL . 'Miss Piggy';
        $this->resolver()->addPath(__DIR__ . '/_templates');
        $renderResult = $this->renderer->render('empty.phtml');
        $this->assertSame($expected, $renderResult);
    }

    public function testCanAccessHelpersInTemplates(): void
    {
        $this->resolver()->addPath(__DIR__ . '/_templates');
        $content = $this->renderer->render('test-with-helpers.phtml');
        foreach (['foo', 'bar', 'baz'] as $value) {
            $this->assertStringContainsString("<li>$value</li>", $content);
        }
    }

    public function testCanSpecifyArrayForVarsAndGetAlwaysArrayObject(): void
    {
        $vars = ['foo' => 'bar'];
        $this->renderer->setVars($vars);
        $this->assertInstanceOf(Variables::class, $this->renderer->vars());
    }

    public function testPassingVariablesObjectToSetVarsShouldUseItDirectory(): void
    {
        $vars = new Variables(['foo' => '<p>Bar</p>']);
        $this->renderer->setVars($vars);
        $this->assertSame($vars, $this->renderer->vars());
    }

    public function testNestedRenderingRestoresVariablesCorrectly(): void
    {
        $expected = "inner\n<p>content</p>";
        $this->resolver()->addPath(__DIR__ . '/_templates');
        $test = $this->renderer->render('testNestedOuter.phtml', ['content' => '<p>content</p>']);
        $this->assertEquals($expected, $test);
    }

    public function testPropertyOverloadingShouldProxyToVariablesContainer(): void
    {
        $this->renderer->foo = '<p>Bar</p>';
        $this->assertEquals($this->renderer->vars('foo'), $this->renderer->foo);
    }

    public function testMethodOverloadingShouldReturnHelperInstanceIfNotInvokable(): void
    {
        /** @psalm-suppress UndefinedMagicMethod */
        $helper = $this->renderer->uninvokable();
        $this->assertInstanceOf(Uninvokable::class, $helper);
    }

    public function testMethodOverloadingShouldInvokeHelperIfInvokable(): void
    {
        /** @psalm-suppress UndefinedMagicMethod */
        $return = $this->renderer->invokable('it works!');
        $this->assertEquals('LaminasTest\View\TestAsset\Invokable::__invoke: it works!', $return);
    }

    public function testGetMethodShouldRetrieveVariableFromVariableContainer(): void
    {
        $this->renderer->foo = '<p>Bar</p>';
        $foo                 = $this->renderer->get('foo');
        $this->assertSame($this->renderer->vars()->foo, $foo);
    }

    public function testRenderingLocalVariables(): void
    {
        $expected = '10 > 9';
        $this->renderer->vars()->assign(['foo' => '10 > 9']);
        $this->resolver()->addPath(__DIR__ . '/_templates');
        $test = $this->renderer->render('testLocalVars.phtml');
        $this->assertStringContainsString($expected, $test);
    }

    public function testRendersTemplatesInAStack(): void
    {
        $resolver = $this->container->get(TemplateMapResolver::class);
        $resolver->setMap([
            'layout' => __DIR__ . '/_templates/layout.phtml',
            'block'  => __DIR__ . '/_templates/block.phtml',
        ]);

        $content = $this->renderer->render('block');
        $this->assertMatchesRegularExpression('#<body>\s*Block content\s*</body>#', $content);
    }

    public function testCanRenderViewModel(): void
    {
        $resolver = $this->container->get(TemplateMapResolver::class);
        $resolver->setMap([
            'empty' => __DIR__ . '/_templates/empty.phtml',
        ]);

        $model = new ViewModel();
        $model->setTemplate('empty');

        $content = $this->renderer->render($model);
        $this->assertMatchesRegularExpression('/\s*Empty view\s*/s', $content);
    }

    public function testViewModelWithoutTemplateRaisesException(): void
    {
        $model = new ViewModel();
        $this->expectException(DomainException::class);
        $this->renderer->render($model);
    }

    public function testRendersViewModelWithVariablesSpecified(): void
    {
        $resolver = $this->container->get(TemplateMapResolver::class);
        $resolver->setMap([
            'test' => __DIR__ . '/_templates/test.phtml',
        ]);

        $model = new ViewModel();
        $model->setTemplate('test');
        $model->setVariable('bar', 'bar');

        $content = $this->renderer->render($model);
        $this->assertMatchesRegularExpression('/\s*foo bar baz\s*/s', $content);
    }

    public function testRenderedViewModelIsRegisteredAsCurrentViewModel(): void
    {
        $resolver = $this->container->get(TemplateMapResolver::class);
        $resolver->setMap([
            'empty' => __DIR__ . '/_templates/empty.phtml',
        ]);

        $model = new ViewModel();
        $model->setTemplate('empty');

        $this->renderer->render($model);
        $helper = $this->renderer->plugin(ViewModelHelper::class);
        $this->assertTrue($helper->hasCurrent());
        $this->assertSame($model, $helper->getCurrent());
    }

    public function testRendererRaisesExceptionInCaseOfExceptionInView(): void
    {
        $resolver = $this->container->get(TemplateMapResolver::class);
        $resolver->setMap([
            'exception' => __DIR__ . '/_templates/exception.phtml',
        ]);

        $model = new ViewModel();
        $model->setTemplate('exception');

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('I was thrown in the view');
        $this->renderer->render($model);
    }

    public function testRendererRaisesExceptionIfResolverCannotResolveTemplate(): void
    {
        $this->renderer->vars()->assign(['foo' => '10 > 9']);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('"should-not-find-this"');
        $this->renderer->render('should-not-find-this');
    }

    /**
     * @return list<array{0: non-empty-string}>
     */
    public static function invalidTemplateFiles(): array
    {
        return [
            ['/does/not/exists'],
            ['.'],
        ];
    }

    /** @param non-empty-string $template */
    #[DataProvider('invalidTemplateFiles')]
    public function testRendererRaisesExceptionIfResolvedTemplateIsInvalid(string $template): void
    {
        $resolver = $this->container->get(TemplateMapResolver::class);
        $resolver->setMap([
            'invalid' => $template,
        ]);

        // @codingStandardsIgnoreStart
        set_error_handler(static fn(int $errno, string $errstr) => true, E_WARNING);
        // @codingStandardsIgnoreEnd

        try {
            $this->renderer->render('invalid');
            $caught = false;
        } catch (Throwable $e) {
            $caught = $e;
        }

        restore_error_handler();
        $this->assertInstanceOf(UnexpectedValueException::class, $caught);
        $this->assertStringContainsString('file include failed', $caught->getMessage());
    }

    public function testIfViewModelComposesVariablesInstanceThenRendererUsesIt(): void
    {
        $resolver = $this->container->get(TemplateMapResolver::class);
        $resolver->setMap([
            'view-model-variables' => __DIR__ . '/_templates/view-model-variables.phtml',
        ]);

        $model = new ViewModel(['foo' => 'BAR-BAZ-BAT']);
        $model->setTemplate('view-model-variables');
        $test = $this->renderer->render($model);
        $this->assertStringContainsString('BAR-BAZ-BAT', $test);
    }

    /**
     * @psalm-suppress UndefinedMagicMethod
     */
    public function testSharedInstanceHelper(): void
    {
        // new instance always created when shared = false
        $this->assertEquals(1, $this->renderer->nonSharedInstance());
        $this->assertEquals(1, $this->renderer->nonSharedInstance());
        $this->assertEquals(1, $this->renderer->nonSharedInstance());

        // use shared instance when shared = true
        $this->assertEquals(1, $this->renderer->sharedInstance());
        $this->assertEquals(2, $this->renderer->sharedInstance());
        $this->assertEquals(3, $this->renderer->sharedInstance());
    }

    public function testContentIsNotMutatedWhenNoFilterHasBeenSet(): void
    {
        $this->resolver()->addPath(__DIR__ . '/_templates');
        $result = $this->renderer->render('empty.phtml');
        self::assertSame('Empty view' . PHP_EOL, $result);
    }

    public function testRendererDoesntUsePreviousRenderedOutputWhenInvokedWithEmptyString(): void
    {
        $this->resolver()->addPath(__DIR__ . '/_templates');

        $previousOutput = $this->renderer->render('empty.phtml');

        /** @psalm-suppress InvalidArgument */
        $actual = $this->renderer->render('');

        $this->assertNotSame($previousOutput, $actual);
    }
}
