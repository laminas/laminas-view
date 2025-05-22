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
use Laminas\View\HelperPluginManager;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplateMapResolver;
use Laminas\View\Resolver\TemplatePathStack;
use Laminas\View\Variables;
use LaminasTest\View\TestAsset\Invokable;
use LaminasTest\View\TestAsset\SharedInstance;
use LaminasTest\View\TestAsset\Uninvokable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Throwable;

use function assert;
use function realpath;
use function restore_error_handler;
use function set_error_handler;

use const E_WARNING;
use const PHP_EOL;

final class PhpRendererTest extends TestCase
{
    private PhpRenderer $renderer;

    protected function setUp(): void
    {
        $this->renderer = new PhpRenderer();
    }

    public function testEngineIsIdenticalToRenderer(): void
    {
        $this->assertSame($this->renderer, $this->renderer->getEngine());
    }

    public function testUsesTemplatePathStackAsDefaultResolver(): void
    {
        $this->assertInstanceOf(TemplatePathStack::class, $this->renderer->resolver());
    }

    public function testCanSetResolverInstance(): void
    {
        $resolver = new TemplatePathStack();
        $this->renderer->setResolver($resolver);
        $this->assertSame($resolver, $this->renderer->resolver());
    }

    private function resolver(): TemplatePathStack
    {
        $resolver = $this->renderer->resolver();
        assert($resolver instanceof TemplatePathStack);

        return $resolver;
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

    public function testUsesHelperPluginManagerByDefault(): void
    {
        $this->assertInstanceOf(HelperPluginManager::class, $this->renderer->getHelperPluginManager());
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
        $helpers = new HelperPluginManager(new ServiceManager(), [
            'invokables' => [
                'uninvokable' => Uninvokable::class,
            ],
        ]);
        $this->renderer->setHelperPluginManager($helpers);
        /** @psalm-suppress UndefinedMagicMethod */
        $helper = $this->renderer->uninvokable();
        $this->assertInstanceOf(Uninvokable::class, $helper);
    }

    public function testMethodOverloadingShouldInvokeHelperIfInvokable(): void
    {
        $helpers = new HelperPluginManager(new ServiceManager(), [
            'invokables' => [
                'invokable' => Invokable::class,
            ],
        ]);
        $this->renderer->setHelperPluginManager($helpers);
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
        $resolver = new TemplateMapResolver([
            'layout' => __DIR__ . '/_templates/layout.phtml',
            'block'  => __DIR__ . '/_templates/block.phtml',
        ]);
        $this->renderer->setResolver($resolver);

        $content = $this->renderer->render('block');
        $this->assertMatchesRegularExpression('#<body>\s*Block content\s*</body>#', $content);
    }

    public function testCanRenderViewModel(): void
    {
        $resolver = new TemplateMapResolver([
            'empty' => __DIR__ . '/_templates/empty.phtml',
        ]);
        $this->renderer->setResolver($resolver);

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
        $resolver = new TemplateMapResolver([
            'test' => __DIR__ . '/_templates/test.phtml',
        ]);
        $this->renderer->setResolver($resolver);

        $model = new ViewModel();
        $model->setTemplate('test');
        $model->setVariable('bar', 'bar');

        $content = $this->renderer->render($model);
        $this->assertMatchesRegularExpression('/\s*foo bar baz\s*/s', $content);
    }

    public function testRenderedViewModelIsRegisteredAsCurrentViewModel(): void
    {
        $resolver = new TemplateMapResolver([
            'empty' => __DIR__ . '/_templates/empty.phtml',
        ]);
        $this->renderer->setResolver($resolver);

        $model = new ViewModel();
        $model->setTemplate('empty');

        $this->renderer->render($model);
        $helper = $this->renderer->plugin(ViewModelHelper::class);
        $this->assertTrue($helper->hasCurrent());
        $this->assertSame($model, $helper->getCurrent());
    }

    public function testRendererRaisesExceptionInCaseOfExceptionInView(): void
    {
        $resolver = new TemplateMapResolver([
            'exception' => __DIR__ . '/_templates/exception.phtml',
        ]);
        $this->renderer->setResolver($resolver);

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
        $resolver = new TemplateMapResolver([
            'invalid' => $template,
        ]);

        // @codingStandardsIgnoreStart
        set_error_handler(static fn(int $errno, string $errstr) => true, E_WARNING);
        // @codingStandardsIgnoreEnd

        $this->renderer->setResolver($resolver);

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

    public function testDoesNotRenderTreesOfViewModelsByDefault(): void
    {
        $this->assertFalse($this->renderer->canRenderTrees());
    }

    public function testRenderTreesOfViewModelsCapabilityIsMutable(): void
    {
        $this->renderer->setCanRenderTrees(true);
        $this->assertTrue($this->renderer->canRenderTrees());
        $this->renderer->setCanRenderTrees(false);
        $this->assertFalse($this->renderer->canRenderTrees());
    }

    public function testIfViewModelComposesVariablesInstanceThenRendererUsesIt(): void
    {
        $model = new ViewModel();
        $model->setTemplate('template');
        $vars        = $model->getVariables();
        $vars['foo'] = 'BAR-BAZ-BAT';

        $resolver = new TemplateMapResolver([
            'template' => __DIR__ . '/_templates/view-model-variables.phtml',
        ]);
        $this->renderer->setResolver($resolver);
        $test = $this->renderer->render($model);
        $this->assertStringContainsString('BAR-BAZ-BAT', $test);
    }

    /**
     * @psalm-suppress UndefinedMagicMethod
     */
    public function testSharedInstanceHelper(): void
    {
        $helpers = new HelperPluginManager(new ServiceManager(), [
            'invokables' => [
                'sharedinstance' => SharedInstance::class,
            ],
            'shared'     => [
                'sharedinstance' => false,
            ],
        ]);
        $this->renderer->setHelperPluginManager($helpers);

        // new instance always created when shared = false
        $this->assertEquals(1, $this->renderer->sharedinstance());
        $this->assertEquals(1, $this->renderer->sharedinstance());
        $this->assertEquals(1, $this->renderer->sharedinstance());

        $helpers = new HelperPluginManager(new ServiceManager(), [
            'invokables' => [
                'sharedinstance' => SharedInstance::class,
            ],
            'shared'     => [
                'sharedinstance' => true,
            ],
        ]);
        $this->renderer->setHelperPluginManager($helpers);
        // use shared instance when shared = true
        $this->assertEquals(1, $this->renderer->sharedinstance());
        $this->assertEquals(2, $this->renderer->sharedinstance());
        $this->assertEquals(3, $this->renderer->sharedinstance());
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

        $actual = $this->renderer->render('');

        $this->assertNotSame($previousOutput, $actual);
    }

    public function testRendererDoesntUsePreviousRenderedOutputWhenInvokedWithFalse(): void
    {
        $this->resolver()->addPath(__DIR__ . '/_templates');

        $previousOutput = $this->renderer->render('empty.phtml');

        /** @psalm-suppress InvalidArgument */
        $actual = $this->renderer->render(false);

        $this->assertNotSame($previousOutput, $actual);
    }
}
