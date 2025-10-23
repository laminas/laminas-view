<?php

declare(strict_types=1);

namespace LaminasTest\View;

use Laminas\ServiceManager\ServiceManager;
use Laminas\View\ConfigProvider;
use Laminas\View\Model\ViewModel;
use Laminas\View\View;
use PHPUnit\Framework\TestCase;

use function array_merge_recursive;
use function preg_replace;
use function trim;

/** @psalm-import-type ViewConfigShape from ConfigProvider */
final class ViewTest extends TestCase
{
    /** @param ViewConfigShape|array<never, never> $config */
    private static function createView(array $config = []): View
    {
        /** Default template directory for the majority of tests */
        $defaults                           = [
            'view_manager' => [
                'template_path_stack' => [
                    __DIR__ . '/templates/view',
                ],
                'template_map'        => [
                    'layout::default' => __DIR__ . '/templates/view/default-layout.phtml',
                    'layout::other'   => __DIR__ . '/templates/view/other-layout.phtml',
                ],
                'default_layout'      => 'layout::default',
            ],
        ];
        $config                             = array_merge_recursive(
            (new ConfigProvider())->__invoke(),
            $defaults,
            $config,
        );
        $config['dependencies']['services'] = ['config' => $config];
        $serviceManager                     = new ServiceManager($config['dependencies']);

        return $serviceManager->get(View::class);
    }

    public function testRenderWithDefaultLayoutAndStringTemplate(): void
    {
        $view    = self::createView();
        $content = $view->renderTemplate('single-variable', ['message' => 'Hey There!']);

        self::assertStringStartsWith('<default-layout>', $content);
        self::assertStringEndsWith('</default-layout>', trim($content));
        self::assertStringContainsString('<p>Hey There!</p>', $content);
    }

    public function testRenderWithSimplePreparedViewModelAndDefaultLayout(): void
    {
        $view  = self::createView();
        $model = new ViewModel(['message' => 'Hey There!']);
        $model->setTemplate('single-variable');

        $content = $view->render($model);

        self::assertStringStartsWith('<default-layout>', $content);
        self::assertStringEndsWith('</default-layout>', trim($content));
        self::assertStringContainsString('<p>Hey There!</p>', $content);
    }

    public function testLayoutIsDisabledWhenTheModelIsMarkedAsTerminal(): void
    {
        $view  = self::createView();
        $model = new ViewModel(['message' => 'Hey There!']);
        $model->setTemplate('single-variable');
        $model->setTerminal(true);

        $content = $view->render($model);

        self::assertStringStartsNotWith('<default-layout>', $content);
        self::assertStringEndsNotWith('</default-layout>', trim($content));
        self::assertStringContainsString('<p>Hey There!</p>', $content);
    }

    public function testLayoutCanBeChangedInsideTemplatesViaTheLayoutViewHelper(): void
    {
        $view    = self::createView();
        $content = $view->renderTemplate('switch-layout', ['message' => 'Hey There!']);

        self::assertStringStartsWith('<other-layout>', $content);
        self::assertStringEndsWith('</other-layout>', trim($content));
        self::assertStringContainsString('<p>Hey There!</p>', $content);
    }

    public function testLayoutCanBeDisabledInsideTemplatesViaTheLayoutHelper(): void
    {
        $view    = self::createView();
        $content = $view->renderTemplate('disable-layout', ['message' => 'Hey There!']);

        self::assertStringStartsNotWith('<other-layout>', $content);
        self::assertStringEndsNotWith('</other-layout>', trim($content));
        self::assertStringContainsString('<p>Hey There!</p>', $content);
    }

    public function testLayoutVarsCanBeMutatedFromTemplateContextViaLayoutHelper(): void
    {
        $view    = self::createView();
        $content = $view->renderTemplate('mutates-layout-vars');

        self::assertStringStartsWith('<layout>', $content);
        self::assertStringEndsWith('</layout>', trim($content));
        self::assertStringContainsString('EXAMPLE', $content);
        self::assertStringContainsString('<content />', $content);
    }

    public function testLayoutSwitchingInsideTemplatesDoesNotAffectSubsequentRendersUsingTheDefaultLayout(): void
    {
        $view          = self::createView();
        $otherLayout   = $view->renderTemplate('switch-layout', ['message' => 'Render 1']);
        $defaultLayout = $view->renderTemplate('single-variable', ['message' => 'Render 2']);

        self::assertStringStartsWith('<other-layout>', $otherLayout);
        self::assertStringStartsWith('<default-layout>', $defaultLayout);

        self::assertStringContainsString('<p>Render 1</p>', $otherLayout);
        self::assertStringContainsString('<p>Render 2</p>', $defaultLayout);
    }

    public function testBasicNestingOfViewModels(): void
    {
        $level2 = (new ViewModel())->setTemplate('basic-nesting-level-2');
        $level1 = (new ViewModel())->setTemplate('basic-nesting-level-1');
        $level1->addChild($level2);

        $view    = self::createView();
        $content = $view->render($level1);

        self::assertStringStartsWith('<default-layout>', $content);
        self::assertStringContainsString('<level-one>', $content);
        self::assertStringContainsString('<level-two>', $content);
    }

    public function testNestedAppendingViewModelsWillBeAggregated(): void
    {
        $level1 = (new ViewModel())->setTemplate('basic-nesting-level-1');

        $a = (new ViewModel(['message' => 'Message-A']))
            ->setTemplate('single-variable')
            ->setAppend(true);
        $b = (new ViewModel(['message' => 'Message-B']))
            ->setTemplate('single-variable')
            ->setAppend(true);

        $level1->addChild($a);
        $level1->addChild($b);

        $view    = self::createView();
        $content = $view->render($level1);

        $expect  = '<default-layout><level-one><p>Message-A</p><p>Message-B</p></level-one></default-layout>';
        $content = preg_replace('/\s+/', '', $content);

        self::assertSame($expect, $content);
    }

    public function testMutatingAppendDuringAddChild(): void
    {
        $level1 = (new ViewModel())->setTemplate('basic-nesting-level-1');

        $a = (new ViewModel(['message' => 'Message-A']))
            ->setTemplate('single-variable')
            ->setAppend(true);
        $b = (new ViewModel(['message' => 'Message-B']))
            ->setTemplate('single-variable')
            ->setAppend(true);

        $level1->addChild($a, null, false);
        $level1->addChild($b, null, false);

        $view    = self::createView();
        $content = $view->render($level1);

        $expect  = '<default-layout><level-one><p>Message-B</p></level-one></default-layout>';
        $content = preg_replace('/\s+/', '', $content);

        self::assertSame($expect, $content);
    }

    public function testNestedModelsClobberExistingValuesForCaptureToVariableName(): void
    {
        $level1 = (new ViewModel([
            'content' => 'This should be over-written',
        ]))->setTemplate('basic-nesting-level-1');
        $a      = (new ViewModel(['message' => 'Message 1']))->setTemplate('single-variable');
        $level1->addChild($a);

        $view    = self::createView();
        $content = $view->render($level1);

        self::assertStringContainsString('<p>Message 1</p>', $content);
        self::assertStringNotContainsString('This should be over-written', $content);
    }

    public function testExistingStringVariablesWillNotBeClobberedWhenTheChildIsAppending(): void
    {
        $level1 = (new ViewModel([
            'content' => 'This should be retained',
        ]))->setTemplate('basic-nesting-level-1');
        $a      = (new ViewModel(['message' => 'Message 1']))->setTemplate('single-variable');
        $level1->addChild($a, null, true);

        $view    = self::createView();
        $content = $view->render($level1);

        self::assertStringContainsString('<p>Message 1</p>', $content);
        self::assertStringContainsString('This should be retained', $content);
    }

    public function testExistingNonStringVariablesWillBeClobberedWhenTheChildIsAppending(): void
    {
        $level1 = (new ViewModel([
            'content' => ['This array will be clobbered'],
        ]))->setTemplate('basic-nesting-level-1');
        $a      = (new ViewModel(['message' => 'Message 1']))->setTemplate('single-variable');
        $level1->addChild($a, null, true);

        $view    = self::createView();
        $content = $view->render($level1);

        $expect  = '<default-layout><level-one><p>Message1</p></level-one></default-layout>';
        $content = preg_replace('/\s+/', '', $content);

        self::assertSame($expect, $content);
    }

    public function testThatTheViewModelHelperIsMutatedWithTheCurrentModel(): void
    {
        /**
         * This test performs assertions inside the template
         *
         * @see ./templates/view/view-model-helper-assertion.phtml
         */

        $view    = self::createView();
        $content = $view->renderTemplate('view-model-helper-assertion', ['message' => 'Message 1']);

        self::assertStringContainsString('<p>Message 1</p>', $content);
        self::assertStringContainsString('<default-layout>', $content);
    }
}
