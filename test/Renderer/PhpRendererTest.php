<?php

declare(strict_types=1);

namespace LaminasTest\View\Renderer;

use ArrayObject;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Exception\RenderingFailedException;
use Laminas\View\Helper\ViewModel as ViewModelHelper;
use Laminas\View\HelperPluginManager;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\ResolverInterface;
use Laminas\View\Resolver\TemplateMapResolver;
use LaminasTest\View\GenerateServiceManager;
use LaminasTest\View\TestAsset\Invokable;
use LaminasTest\View\TestAsset\SharedInstance;
use LaminasTest\View\TestAsset\Uninvokable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;

use function restore_error_handler;
use function sprintf;

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
                    'uninvokable'       => new Uninvokable(),
                    'invokable'         => new Invokable(),
                    'exceptionalHelper' => static function (): never {
                        throw new RuntimeException('A helper exception');
                    },
                ],
                'factories' => [
                    'sharedInstance'    => fn (): SharedInstance => new SharedInstance(),
                    'nonSharedInstance' => fn (): SharedInstance => new SharedInstance(),
                ],
                'shared'    => [
                    'sharedInstance'    => true,
                    'nonSharedInstance' => false,
                ],
            ],
            'view_manager' => [
                'template_path_stack' => [
                    __DIR__ . '/templates',
                ],
            ],
        ]);

        $this->renderer = $this->container->get(PhpRenderer::class);
    }

    public function testBasicRenderOfStaticContent(): void
    {
        self::assertStringContainsString(
            '<p>Static Content</p>',
            $this->renderer->render('static-content'),
        );
    }

    public function testVariablesCanBeAccessedAsInstanceProperties(): void
    {
        self::assertStringContainsString(
            '<p>Kermit</p>',
            $this->renderer->render('variable-as-property', ['message' => 'Kermit']),
        );
    }

    public function testVariablesCanBeAccessedInLocalScope(): void
    {
        self::assertStringContainsString(
            '<p>Miss Piggy</p>',
            $this->renderer->render('variable-in-local-scope', ['message' => 'Miss Piggy']),
        );
    }

    public function testViewHelpersExecuteAsExpected(): void
    {
        self::assertStringContainsString(
            '<p>Miss Piggy &amp; Kermit</p>',
            $this->renderer->render('escaped-variable', ['message' => 'Miss Piggy & Kermit']),
        );
    }

    public function testAccessToUndefinedVariablesIsExceptional(): void
    {
        $this->expectException(RenderingFailedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Access to an undeclared variable "message" in the template');
        $this->renderer->render('variable-as-property');
    }

    public function testAccessToUndefinedVariablesIsNotExceptionalWhenStrictVariablesIsOff(): void
    {
        $renderer = new PhpRenderer(
            $this->container->get(HelperPluginManager::class),
            $this->container->get(ResolverInterface::class),
            false,
        );

        self::assertStringContainsString(
            '<p></p>',
            $renderer->render('variable-as-property'),
        );
    }

    public function testCallsToUnknownHelpersAreExceptional(): void
    {
        $this->expectException(RenderingFailedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            'Access to an unknown view helper alias "notAKnownHelperAlias" from the template',
        );
        $this->renderer->render('undefined-helper');
    }

    public function testExceptionsThrownInViewHelpersAreWrapped(): void
    {
        $this->expectException(RenderingFailedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            'An exception occurred during execution of the plugin "exceptionalHelper". Message: A helper exception',
        );
        $this->renderer->render('exceptional-helper');
    }

    /** @return iterable<string, array{0: iterable<string, mixed>}> */
    public static function possibleViewVariableTypes(): iterable
    {
        yield 'Basic Array' => [['message' => 'Example Message']];

        yield 'Array Object' => [new ArrayObject(['message' => 'Example Message'])];
    }

    /** @param iterable<string, mixed> $type */
    #[DataProvider('possibleViewVariableTypes')]
    public function testViewVariablesArgumentWithPossibleTypes(iterable $type): void
    {
        $content = $this->renderer->render('variable-as-property', $type);

        self::assertStringContainsString('<p>Example Message</p>', $content);
    }

    public function testFilterCanBeAddedToMutateOutput(): void
    {
        $filter = static fn (string $content): string => $content . 'foo';
        $this->renderer->setFilter($filter);
        $output = $this->renderer->render('static-content');
        self::assertSame('<p>Static Content</p>' . PHP_EOL . 'foo', $output);
    }

    public function testMethodOverloadingShouldReturnHelperInstanceIfNotInvokable(): void
    {
        self::assertStringContainsString(
            '<p>' . (new Uninvokable())->value . '</p>',
            $this->renderer->render('call-uninvokable-helper'),
        );
    }

    public function testMethodOverloadingShouldInvokeHelperIfInvokable(): void
    {
        self::assertStringContainsString(
            '<p>LaminasTest\View\TestAsset\Invokable::__invoke: Muppets</p>',
            $this->renderer->render('call-invokable-helper', ['message' => 'Muppets']),
        );
    }

    public function testCanRenderViewModel(): void
    {
        $model = new ViewModel();
        $model->setTemplate('static-content');
        $content = $this->renderer->render($model);

        self::assertStringContainsString('<p>Static Content</p>', $content);
    }

    public function testViewModelWithoutTemplateRaisesException(): void
    {
        $model = new ViewModel();
        $this->expectException(RenderingFailedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            'A template must be specified during rendering, either as an argument or as a property of the view model',
        );
        $this->renderer->render($model);
    }

    public function testRendersViewModelWithVariablesSpecified(): void
    {
        $model = new ViewModel();
        $model->setTemplate('variable-as-property');
        $model->setVariable('message', 'Whatever');

        $content = $this->renderer->render($model);
        self::assertStringContainsString('<p>Whatever</p>', $content);
    }

    public function testRenderedViewModelIsRegisteredAsTheCurrentViewModel(): void
    {
        $model = new ViewModel();
        $model->setTemplate('static-content');
        $this->renderer->render($model);

        $plugins = $this->container->get(HelperPluginManager::class);
        $helper  = $plugins->get(ViewModelHelper::class);

        $this->assertTrue($helper->hasCurrent());
        $this->assertSame($model, $helper->getCurrent());
    }

    public function testRendererRaisesExceptionInCaseOfExceptionInView(): void
    {
        $model = new ViewModel();
        $model->setTemplate('local-exception');

        $this->expectException(RenderingFailedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('local-exception.phtml" with the message: I was thrown in the view');
        $this->renderer->render($model);
    }

    public function testRendererRaisesExceptionIfResolverCannotResolveTemplate(): void
    {
        $this->expectException(RenderingFailedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            'Unable to render template "should-not-find-this"; resolver could not resolve to a file',
        );
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
        /** @psalm-suppress UnusedClosureParam */
        set_error_handler(static fn(int $errno, string $errstr): bool => true, E_WARNING);
        // @codingStandardsIgnoreEnd

        try {
            $this->renderer->render('invalid');
            $caught = false;
        } catch (Throwable $e) {
            $caught = $e;
        }

        restore_error_handler();
        $this->assertInstanceOf(RenderingFailedException::class, $caught);
        $this->assertStringContainsString(sprintf(
            'Failed to render template because the template file could not be included: "%s"',
            $template,
        ), $caught->getMessage());
    }

    public function testVariablesArgumentIsIgnoredWhenAViewModelIsGiven(): void
    {
        $model = new ViewModel(['message' => 'View Model Variable']);
        $model->setTemplate('variable-as-property');

        self::assertStringContainsString(
            '<p>View Model Variable</p>',
            $this->renderer->render($model, ['message' => 'Variable from arguments']),
        );
    }

    public function testSharedInstanceHelper(): void
    {
        $content = $this->renderer->render('shared-helper');
        self::assertStringContainsString(
            '<p>Shared: 1 2 3</p>',
            $content,
        );
        self::assertStringContainsString(
            '<p>Un-shared: 1 1 1</p>',
            $content,
        );
    }

    public function testAnEmptyTemplateNameIsExceptional(): void
    {
        $this->expectException(RenderingFailedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            'A template must be specified during rendering, either as an argument or as a property of the view model',
        );

        /** @psalm-suppress InvalidArgument */
        $this->renderer->render('');
    }

    public function testThatInfiniteRenderLoopIsStoppedViaException(): void
    {
        $this->expectException(RenderingFailedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('A cyclic rendering dependency has been detected during render of the template');

        $this->renderer->render('infinite-render-loop.phtml');
    }

    public function testViewVariablePropertiesCannotBeMutatedInTheTemplate(): void
    {
        $this->expectException(RenderingFailedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Attempt to mutate the variable "message" in the template');
        $this->renderer->render('variable-mutation', ['message' => 'Some Message']);
    }

    public function testUndefinedVariablesCanBeTestedAndCoalescedFromWithinTemplates(): void
    {
        self::assertStringContainsString(
            '<p>Default Message</p>',
            $this->renderer->render('undefined-variable-condition'),
        );

        self::assertStringContainsString(
            '<p>Custom Message</p>',
            $this->renderer->render('undefined-variable-condition', ['message' => 'Custom Message']),
        );
    }

    public function testThatInvokableObjectsAreNotInvokedOnAccess(): void
    {
        $invokable = new class {
            public function __invoke(): string
            {
                return 'INVOKE';
            }

            public function __toString(): string
            {
                return 'STRING';
            }
        };

        $content = $this->renderer->render('invokable-variable', ['item' => $invokable]);

        self::assertStringContainsString(
            '<cast-to-string>STRING</cast-to-string>',
            $content,
        );
        self::assertStringContainsString(
            '<invoke>INVOKE</invoke>',
            $content,
        );
    }
}
