<?php

declare(strict_types=1);

namespace LaminasTest\View\Renderer;

use ArrayObject;
use Laminas\View\Exception;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\JsonRenderer;
use PHPUnit\Framework\TestCase;
use stdClass;

use function get_object_vars;
use function json_encode;

use const JSON_THROW_ON_ERROR;

class JsonRendererTest extends TestCase
{
    /** @var JsonRenderer */
    protected $renderer;

    protected function setUp(): void
    {
        $this->renderer = new JsonRenderer();
    }

    public function testRendersViewModelsWithoutChildren(): void
    {
        $model = new ViewModel(['foo' => 'bar']);
        $test  = $this->renderer->render($model);
        $this->assertEquals(json_encode(['foo' => 'bar']), $test);
    }

    public function testRendersViewModelsWithChildrenUsingCaptureToValue(): void
    {
        $root   = new ViewModel(['foo' => 'bar']);
        $child1 = new ViewModel(['foo' => 'bar']);
        $child2 = new ViewModel(['foo' => 'bar']);
        $child1->setCaptureTo('child1');
        $child2->setCaptureTo('child2');
        $root->addChild($child1)
             ->addChild($child2);

        $expected = [
            'foo'    => 'bar',
            'child1' => [
                'foo' => 'bar',
            ],
            'child2' => [
                'foo' => 'bar',
            ],
        ];
        $test     = $this->renderer->render($root);
        $this->assertEquals(json_encode($expected), $test);
    }

    public function testThrowsAwayChildModelsWithoutCaptureToValueByDefault(): void
    {
        $root   = new ViewModel(['foo' => 'bar']);
        $child1 = new ViewModel(['foo' => 'baz']);
        $child2 = new ViewModel(['foo' => 'bar']);
        $child1->setCaptureTo(false);
        $child2->setCaptureTo('child2');
        $root->addChild($child1)
             ->addChild($child2);

        $expected = [
            'foo'    => 'bar',
            'child2' => [
                'foo' => 'bar',
            ],
        ];
        $test     = $this->renderer->render($root);
        $this->assertEquals(json_encode($expected), $test);
    }

    public function testCanMergeChildModelsWithoutCaptureToValues(): void
    {
        $this->renderer->setMergeUnnamedChildren(true);
        $root   = new ViewModel(['foo' => 'bar']);
        $child1 = new ViewModel(['foo' => 'baz']);
        $child2 = new ViewModel(['foo' => 'bar']);
        $child1->setCaptureTo(false);
        $child2->setCaptureTo('child2');
        $root->addChild($child1)
             ->addChild($child2);

        $expected = [
            'foo'    => 'baz',
            'child2' => [
                'foo' => 'bar',
            ],
        ];
        $test     = $this->renderer->render($root);
        $this->assertEquals(json_encode($expected), $test);
    }

    /**
     * @psalm-return array<array-key, array{0: mixed}>
     */
    public function getNonObjectModels(): array
    {
        return [
            ['string'],
            [1],
            [1.0],
            [['foo', 'bar']],
            [['foo' => 'bar']],
        ];
    }

    /**
     * @dataProvider getNonObjectModels
     * @param mixed $model
     */
    public function testRendersNonObjectModelAsJson($model): void
    {
        $expected = json_encode($model, JSON_THROW_ON_ERROR);
        /** @psalm-suppress MixedArgument $test */
        $test = $this->renderer->render($model);
        $this->assertEquals($expected, $test);
    }

    public function testRendersJsonSerializableModelsAsJson(): void
    {
        $model        = new TestAsset\JsonModel();
        $model->value = ['foo' => 'bar'];
        $expected     = json_encode($model->value);
        $test         = $this->renderer->render($model);
        $this->assertEquals($expected, $test);
    }

    public function testRendersTraversableObjectsAsJsonObjects(): void
    {
        $model    = new ArrayObject([
            'foo' => 'bar',
            'bar' => 'baz',
        ]);
        $expected = json_encode($model->getArrayCopy(), JSON_THROW_ON_ERROR);
        $test     = $this->renderer->render($model);
        $this->assertEquals($expected, $test);
    }

    public function testRendersNonTraversableNonJsonSerializableObjectsAsJsonObjects(): void
    {
        $model      = new stdClass();
        $model->foo = 'bar';
        $model->bar = 'baz';
        $expected   = json_encode(get_object_vars($model), JSON_THROW_ON_ERROR);
        $test       = $this->renderer->render($model);
        $this->assertEquals($expected, $test);
    }

    public function testNonViewModelInitialArgumentWithValuesRaisesException(): void
    {
        $this->expectException(Exception\DomainException::class);
        $this->renderer->render('foo', ['bar' => 'baz']);
    }

    public function testRendersTreesOfViewModelsByDefault(): void
    {
        $this->assertTrue($this->renderer->canRenderTrees());
    }

    public function testSetHasJsonpCallback(): void
    {
        $this->assertFalse($this->renderer->hasJsonpCallback());
        $this->renderer->setJsonpCallback(0);
        $this->assertFalse($this->renderer->hasJsonpCallback());
        $this->renderer->setJsonpCallback('callback');
        $this->assertTrue($this->renderer->hasJsonpCallback());
    }

    public function testRendersViewModelsWithoutChildrenWithJsonpCallback(): void
    {
        $model = new ViewModel(['foo' => 'bar']);
        $this->renderer->setJsonpCallback('callback');
        $test     = $this->renderer->render($model);
        $expected = 'callback(' . json_encode(['foo' => 'bar']) . ');';
        $this->assertEquals($expected, $test);
    }

    /**
     * @dataProvider getNonObjectModels
     * @param mixed $model
     */
    public function testRendersNonObjectModelAsJsonWithJsonpCallback($model): void
    {
        $expected = 'callback(' . json_encode($model, JSON_THROW_ON_ERROR) . ');';
        $this->renderer->setJsonpCallback('callback');
        $test = $this->renderer->render($model);
        $this->assertEquals($expected, $test);
    }

    public function testRendersJsonSerializableModelsAsJsonWithJsonpCallback(): void
    {
        $model        = new TestAsset\JsonModel();
        $model->value = ['foo' => 'bar'];
        $expected     = 'callback(' . json_encode($model->value) . ');';
        $this->renderer->setJsonpCallback('callback');
        $test = $this->renderer->render($model);
        $this->assertEquals($expected, $test);
    }

    public function testRendersTraversableObjectsAsJsonObjectsWithJsonpCallback(): void
    {
        $model    = new ArrayObject([
            'foo' => 'bar',
            'bar' => 'baz',
        ]);
        $expected = 'callback(' . json_encode($model->getArrayCopy(), JSON_THROW_ON_ERROR) . ');';
        $this->renderer->setJsonpCallback('callback');
        $test = $this->renderer->render($model);
        $this->assertEquals($expected, $test);
    }

    public function testRendersNonTraversableNonJsonSerializableObjectsAsJsonObjectsWithJsonpCallback(): void
    {
        $model      = new stdClass();
        $model->foo = 'bar';
        $model->bar = 'baz';
        $expected   = 'callback(' . json_encode(get_object_vars($model), JSON_THROW_ON_ERROR) . ');';
        $this->renderer->setJsonpCallback('callback');
        $test = $this->renderer->render($model);
        $this->assertEquals($expected, $test);
    }

    public function testRecursesJsonModelChildrenWhenRendering(): void
    {
        $root   = new JsonModel(['foo' => 'bar']);
        $child1 = new JsonModel(['foo' => 'bar']);
        $child2 = new JsonModel(['foo' => 'bar']);
        $child1->setCaptureTo('child1');
        $child2->setCaptureTo('child2');
        $root->addChild($child1)
             ->addChild($child2);

        $expected = [
            'foo'    => 'bar',
            'child1' => [
                'foo' => 'bar',
            ],
            'child2' => [
                'foo' => 'bar',
            ],
        ];
        $test     = $this->renderer->render($root);
        $this->assertEquals(json_encode($expected), $test);
    }
}
