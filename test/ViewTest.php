<?php

declare(strict_types=1);

namespace LaminasTest\View;

use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\View\Exception;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver;
use Laminas\View\Variables as ViewVariables;
use Laminas\View\View;
use Laminas\View\ViewEvent;
use PHPUnit\Framework\TestCase;
use stdClass;

use function json_encode;
use function sprintf;
use function var_export;

class ViewTest extends TestCase
{
    /** @var stdClass */
    private $result;
    private Request $request;
    private Response $response;
    private ViewModel $model;
    private View $view;

    protected function setUp(): void
    {
        $this->request  = new Request();
        $this->response = new Response();
        $this->model    = new ViewModel();
        $this->view     = new View();

        $this->view->setRequest($this->request);
        $this->view->setResponse($this->response);
    }

    public function attachTestStrategies(): void
    {
        $this->view->addRenderingStrategy(static fn() => new TestAsset\Renderer\VarExportRenderer());
        $this->result = $result = new stdClass();
        $this->view->addResponseStrategy(function (ViewEvent $e) use ($result) {
            $result->content = $e->getResult();
        });
    }

    public function testRendersViewModelWithNoChildren(): void
    {
        $this->attachTestStrategies();
        $variables = [
            'foo' => 'bar',
            'bar' => 'baz',
        ];
        $this->model->setVariables($variables);
        $this->view->render($this->model);

        foreach ($variables as $key => $value) {
            $expect = sprintf("'%s' => '%s',", $key, $value);
            $this->assertStringContainsString($expect, $this->result->content);
        }
    }

    public function testRendersViewModelWithChildren(): void
    {
        $this->attachTestStrategies();

        $child1 = new ViewModel(['foo' => 'bar']);

        $child2 = new ViewModel(['bar' => 'baz']);

        $this->model->setVariable('parent', 'node');
        $this->model->addChild($child1, 'child1');
        $this->model->addChild($child2, 'child2');

        $this->view->render($this->model);

        $expected = var_export(new ViewVariables([
            'parent' => 'node',
            'child1' => var_export(['foo' => 'bar'], true),
            'child2' => var_export(['bar' => 'baz'], true),
        ]), true);
        $this->assertEquals($expected, $this->result->content);
    }

    public function testRendersTreeOfModels(): void
    {
        $this->attachTestStrategies();

        $child1 = new ViewModel(['foo' => 'bar']);
        $child1->setCaptureTo('child1');

        $child2 = new ViewModel(['bar' => 'baz']);
        $child2->setCaptureTo('child2');
        $child1->addChild($child2);

        $this->model->setVariable('parent', 'node');
        $this->model->addChild($child1);

        $this->view->render($this->model);

        $expected = var_export(new ViewVariables([
            'parent' => 'node',
            'child1' => var_export([
                'foo'    => 'bar',
                'child2' => var_export(['bar' => 'baz'], true),
            ], true),
        ]), true);
        $this->assertEquals($expected, $this->result->content);
    }

    public function testChildrenMayInvokeDifferentRenderingStrategiesThanParents(): void
    {
        $this->view->addRenderingStrategy(function (ViewEvent $e) {
            $model = $e->getModel();
            if (! $model instanceof ViewModel) {
                return;
            }
            return new TestAsset\Renderer\VarExportRenderer();
        });
        $this->view->addRenderingStrategy(function (ViewEvent $e) {
            $model = $e->getModel();
            if (! $model instanceof JsonModel) {
                return;
            }
            return new Renderer\JsonRenderer();
        }, 10); // higher priority, so it matches earlier
        $this->result = $result = new stdClass();
        $this->view->addResponseStrategy(function (ViewEvent $e) use ($result) {
            $result->content = $e->getResult();
        });

        $child1 = new ViewModel(['foo' => 'bar']);
        $child1->setCaptureTo('child1');

        $child2 = new JsonModel(['bar' => 'baz']);
        $child2->setCaptureTo('child2');
        $child2->setTerminal(false);

        $this->model->setVariable('parent', 'node');
        $this->model->addChild($child1);
        $this->model->addChild($child2);

        $this->view->render($this->model);

        $expected = var_export(new ViewVariables([
            'parent' => 'node',
            'child1' => var_export(['foo' => 'bar'], true),
            'child2' => json_encode(['bar' => 'baz']),
        ]), true);
        $this->assertEquals($expected, $this->result->content);
    }

    public function testTerminalChildRaisesException(): void
    {
        $this->attachTestStrategies();

        $child1 = new ViewModel(['foo' => 'bar']);
        $child1->setCaptureTo('child1');
        $child1->setTerminal(true);

        $this->model->setVariable('parent', 'node');
        $this->model->addChild($child1);

        $this->expectException(Exception\DomainException::class);
        $this->view->render($this->model);
    }

    public function testChildrenAreCapturedToParentVariables(): void
    {
        // I wish there were a "markTestRedundant()" method in PHPUnit
        $this->testRendersViewModelWithChildren();
    }

    public function testOmittingCaptureToValueInChildLeadsToOmissionInParent(): void
    {
        $this->attachTestStrategies();

        $child1 = new ViewModel(['foo' => 'bar']);
        $child1->setCaptureTo('child1');

        // Deliberately disable the "capture to" declaration
        $child2 = new ViewModel(['bar' => 'baz']);
        $child2->setCaptureTo(null);

        $this->model->setVariable('parent', 'node');
        $this->model->addChild($child1);
        $this->model->addChild($child2);

        $this->view->render($this->model);

        $expected = var_export(new ViewVariables([
            'parent' => 'node',
            'child1' => var_export(['foo' => 'bar'], true),
        ]), true);
        $this->assertEquals($expected, $this->result->content);
    }

    public function testResponseStrategyIsTriggeredForParentModel(): void
    {
        // I wish there were a "markTestRedundant()" method in PHPUnit
        $this->testRendersViewModelWithChildren();
    }

    public function testResponseStrategyIsNotTriggeredForChildModel(): void
    {
        $this->view->addRenderingStrategy(static fn() => new Renderer\JsonRenderer());

        $result = [];
        $this->view->addResponseStrategy(function (ViewEvent $e) use (&$result) {
            /** @psalm-var mixed */
            $result[] = $e->getResult();
        });

        $child1 = new ViewModel(['foo' => 'bar']);
        $child1->setCaptureTo('child1');

        $child2 = new ViewModel(['bar' => 'baz']);
        $child2->setCaptureTo('child2');

        $this->model->setVariable('parent', 'node');
        $this->model->addChild($child1);
        $this->model->addChild($child2);

        $this->view->render($this->model);

        self::assertCount(1, $result);
    }

    public function testUsesTreeRendererInterfaceToDetermineWhetherOrNotToPassOnlyRootViewModelToPhpRenderer(): void
    {
        $resolver    = new Resolver\TemplateMapResolver([
            'layout'  => __DIR__ . '/_templates/nested-view-model-layout.phtml',
            'content' => __DIR__ . '/_templates/nested-view-model-content.phtml',
        ]);
        $phpRenderer = new PhpRenderer();
        $phpRenderer->setCanRenderTrees(true);
        $phpRenderer->setResolver($resolver);

        $this->view->addRenderingStrategy(static fn() => $phpRenderer);

        $result = new stdClass();
        $this->view->addResponseStrategy(function (ViewEvent $e) use ($result) {
            $result->content = $e->getResult();
        });

        $layout = new ViewModel();
        $layout->setTemplate('layout');
        $content = new ViewModel();
        $content->setTemplate('content');
        $content->setCaptureTo('content');
        $layout->addChild($content);

        $this->view->render($layout);

        $this->assertStringContainsString('Layout start', $result->content);
        $this->assertStringContainsString('Content for layout', $result->content, $result->content);
        $this->assertStringContainsString('Layout end', $result->content);
    }

    public function testUsesTreeRendererInterfaceToDetermineWhetherOrNotToPassOnlyRootViewModelToJsonRenderer(): void
    {
        $jsonRenderer = new Renderer\JsonRenderer();

        $this->view->addRenderingStrategy(static fn() => $jsonRenderer);

        $result = new stdClass();
        $this->view->addResponseStrategy(function (ViewEvent $e) use ($result) {
            $result->content = $e->getResult();
        });

        $layout  = new ViewModel(['status' => 200]);
        $content = new ViewModel(['foo' => 'bar']);
        $content->setCaptureTo('response');
        $layout->addChild($content);

        $this->view->render($layout);

        $expected = json_encode([
            'status'   => 200,
            'response' => ['foo' => 'bar'],
        ]);

        $this->assertEquals($expected, $result->content);
    }

    public function testCanTriggerPostRendererEvent(): void
    {
        $this->attachTestStrategies();
        $flag = false;
        $this->view->getEventManager()->attach(ViewEvent::EVENT_RENDERER_POST, function () use (&$flag) {
            $flag = true;
        });
        $variables = [
            'foo' => 'bar',
            'bar' => 'baz',
        ];
        $this->model->setVariables($variables);
        $this->view->render($this->model);
        $this->assertTrue($flag);
    }

    /**
     * Test the view model can be swapped out
     *
     * @see https://github.com/zendframework/zf2/pull/4164
     */
    public function testModelFromEventIsUsedByRenderer(): void
    {
        $renderer = $this->createMock(PhpRenderer::class);

        $model1 = new ViewModel();
        $model2 = new ViewModel();

        $renderer->expects($this->once())
            ->method('render')
            ->with($model2);

        $this->view->addRenderingStrategy(static fn() => $renderer);

        $this->view->render($model1);
    }
}
