<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\Renderer;

use ArrayObject;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\JsonRenderer;
use PHPUnit_Framework_TestCase as TestCase;
use stdClass;

/**
 * @category   Laminas
 * @package    Laminas_View
 * @subpackage UnitTests
 * @group      Laminas_View
 */
class JsonRendererTest extends TestCase
{
    /**
     * @var JsonRenderer
     */
    protected $renderer;

    public function setUp()
    {
        $this->renderer = new JsonRenderer();
    }

    public function testRendersViewModelsWithoutChildren()
    {
        $model = new ViewModel(array('foo' => 'bar'));
        $test  = $this->renderer->render($model);
        $this->assertEquals(json_encode(array('foo' => 'bar')), $test);
    }

    public function testRendersViewModelsWithChildrenUsingCaptureToValue()
    {
        $root   = new ViewModel(array('foo' => 'bar'));
        $child1 = new ViewModel(array('foo' => 'bar'));
        $child2 = new ViewModel(array('foo' => 'bar'));
        $child1->setCaptureTo('child1');
        $child2->setCaptureTo('child2');
        $root->addChild($child1)
             ->addChild($child2);

        $expected = array(
            'foo' => 'bar',
            'child1' => array(
                'foo' => 'bar',
            ),
            'child2' => array(
                'foo' => 'bar',
            ),
        );
        $test  = $this->renderer->render($root);
        $this->assertEquals(json_encode($expected), $test);
    }

    public function testThrowsAwayChildModelsWithoutCaptureToValueByDefault()
    {
        $root   = new ViewModel(array('foo' => 'bar'));
        $child1 = new ViewModel(array('foo' => 'baz'));
        $child2 = new ViewModel(array('foo' => 'bar'));
        $child1->setCaptureTo(false);
        $child2->setCaptureTo('child2');
        $root->addChild($child1)
             ->addChild($child2);

        $expected = array(
            'foo' => 'bar',
            'child2' => array(
                'foo' => 'bar',
            ),
        );
        $test  = $this->renderer->render($root);
        $this->assertEquals(json_encode($expected), $test);
    }

    public function testCanMergeChildModelsWithoutCaptureToValues()
    {
        $this->renderer->setMergeUnnamedChildren(true);
        $root   = new ViewModel(array('foo' => 'bar'));
        $child1 = new ViewModel(array('foo' => 'baz'));
        $child2 = new ViewModel(array('foo' => 'bar'));
        $child1->setCaptureTo(false);
        $child2->setCaptureTo('child2');
        $root->addChild($child1)
             ->addChild($child2);

        $expected = array(
            'foo' => 'baz',
            'child2' => array(
                'foo' => 'bar',
            ),
        );
        $test  = $this->renderer->render($root);
        $this->assertEquals(json_encode($expected), $test);
    }

    public function getNonObjectModels()
    {
        return array(
            array('string'),
            array(1),
            array(1.0),
            array(array('foo', 'bar')),
            array(array('foo' => 'bar')),
        );
    }

    /**
     * @dataProvider getNonObjectModels
     */
    public function testRendersNonObjectModelAsJson($model)
    {
        $expected = json_encode($model);
        $test     = $this->renderer->render($model);
        $this->assertEquals($expected, $test);
    }

    public function testRendersJsonSerializableModelsAsJson()
    {
        if (version_compare(PHP_VERSION, '5.4.0', '<')) {
            $this->markTestSkipped('Can only test JsonSerializable models in PHP 5.4.0 and up');
        }
        $model        = new TestAsset\JsonModel;
        $model->value = array('foo' => 'bar');
        $expected     = json_encode($model->value);
        $test         = $this->renderer->render($model);
        $this->assertEquals($expected, $test);
    }

    public function testRendersTraversableObjectsAsJsonObjects()
    {
        $model = new ArrayObject(array(
            'foo' => 'bar',
            'bar' => 'baz',
        ));
        $expected     = json_encode($model->getArrayCopy());
        $test         = $this->renderer->render($model);
        $this->assertEquals($expected, $test);
    }

    public function testRendersNonTraversableNonJsonSerializableObjectsAsJsonObjects()
    {
        $model      = new stdClass;
        $model->foo = 'bar';
        $model->bar = 'baz';
        $expected   = json_encode(get_object_vars($model));
        $test       = $this->renderer->render($model);
        $this->assertEquals($expected, $test);
    }

    public function testNonViewModelInitialArgumentWithValuesRaisesException()
    {
        $this->setExpectedException('Laminas\View\Exception\DomainException');
        $this->renderer->render('foo', array('bar' => 'baz'));
    }

    public function testRendersTreesOfViewModelsByDefault()
    {
        $this->assertTrue($this->renderer->canRenderTrees());
    }

    public function testSetHasJsonpCallback()
    {
        $this->assertFalse($this->renderer->hasJsonpCallback());
        $this->renderer->setJsonpCallback(0);
        $this->assertFalse($this->renderer->hasJsonpCallback());
        $this->renderer->setJsonpCallback('callback');
        $this->assertTrue($this->renderer->hasJsonpCallback());
    }

    public function testRendersViewModelsWithoutChildrenWithJsonpCallback()
    {
        $model = new ViewModel(array('foo' => 'bar'));
        $this->renderer->setJsonpCallback('callback');
        $test = $this->renderer->render($model);
        $expected = 'callback(' . json_encode(array('foo' => 'bar')) . ');';
        $this->assertEquals($expected, $test);
    }

    /**
     * @dataProvider getNonObjectModels
     */
    public function testRendersNonObjectModelAsJsonWithJsonpCallback($model)
    {
        $expected = 'callback(' . json_encode($model) . ');';
        $this->renderer->setJsonpCallback('callback');
        $test = $this->renderer->render($model);
        $this->assertEquals($expected, $test);
    }

    public function testRendersJsonSerializableModelsAsJsonWithJsonpCallback()
    {
        if (version_compare(PHP_VERSION, '5.4.0', '<')) {
            $this->markTestSkipped('Can only test JsonSerializable models in PHP 5.4.0 and up');
        }
        $model        = new TestAsset\JsonModel;
        $model->value = array('foo' => 'bar');
        $expected     = 'callback(' . json_encode($model->value) . ');';
        $this->renderer->setJsonpCallback('callback');
        $test         = $this->renderer->render($model);
        $this->assertEquals($expected, $test);
    }

    public function testRendersTraversableObjectsAsJsonObjectsWithJsonpCallback()
    {
        $model = new ArrayObject(array(
            'foo' => 'bar',
            'bar' => 'baz',
        ));
        $expected     = 'callback(' . json_encode($model->getArrayCopy()) . ');';
        $this->renderer->setJsonpCallback('callback');
        $test         = $this->renderer->render($model);
        $this->assertEquals($expected, $test);
    }

    public function testRendersNonTraversableNonJsonSerializableObjectsAsJsonObjectsWithJsonpCallback()
    {
        $model      = new stdClass;
        $model->foo = 'bar';
        $model->bar = 'baz';
        $expected   = 'callback(' . json_encode(get_object_vars($model)) . ');';
        $this->renderer->setJsonpCallback('callback');
        $test       = $this->renderer->render($model);
        $this->assertEquals($expected, $test);
    }
}
