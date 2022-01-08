<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use ArrayObject;
use Laminas\View\Helper\Partial;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer as View;
use LaminasTest\View\Helper\TestAsset\Aggregate;
use PHPUnit\Framework\TestCase;
use stdClass;

use function get_object_vars;
use function sprintf;

/**
 * Test class for Partial view helper.
 *
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class PartialTest extends TestCase
{
    /** @var Partial */
    public $helper;

    /** @var string */
    public $basePath;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->basePath = __DIR__ . '/_files/modules';
        $this->helper   = new Partial();
    }

    public function testPartialRendersScript(): void
    {
        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);
        $return = $this->helper->__invoke('partialOne.phtml');
        $this->assertStringContainsString('This is the first test partial', $return);
    }

    public function testPartialRendersScriptWithVars(): void
    {
        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $view->vars()->message = 'This should never be read';
        $this->helper->setView($view);
        $return = $this->helper->__invoke('partialThree.phtml', ['message' => 'This message should be read']);
        $this->assertStringNotContainsString('This should never be read', $return);
        $this->assertStringContainsString('This message should be read', $return, $return);
    }

    public function testSetViewSetsViewProperty(): void
    {
        $view = new View();
        $this->helper->setView($view);
        $this->assertSame($view, $this->helper->getView());
    }

    public function testObjectModelWithPublicPropertiesSetsViewVariables(): void
    {
        $model      = new stdClass();
        $model->foo = 'bar';
        $model->bar = 'baz';

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);
        $return = $this->helper->__invoke('partialVars.phtml', $model);

        foreach (get_object_vars($model) as $key => $value) {
            $string = sprintf('%s: %s', $key, $value);
            $this->assertStringContainsString($string, $return);
        }
    }

    public function testObjectModelWithToArraySetsViewVariables(): void
    {
        $model = new Aggregate();

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);
        $return = $this->helper->__invoke('partialVars.phtml', $model);

        foreach ($model->toArray() as $key => $value) {
            $string = sprintf('%s: %s', $key, $value);
            $this->assertStringContainsString($string, $return);
        }
    }

    public function testPassingNoArgsReturnsHelperInstance(): void
    {
        $test = $this->helper->__invoke();
        $this->assertSame($this->helper, $test);
    }

    public function testCanPassViewModelAsSecondArgument(): void
    {
        $model = new ViewModel([
            'foo' => 'bar',
            'bar' => 'baz',
        ]);

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);
        $return = $this->helper->__invoke('partialVars.phtml', $model);

        foreach ($model->getVariables() as $key => $value) {
            $string = sprintf('%s: %s', $key, $value);
            $this->assertStringContainsString($string, $return);
        }
    }

    public function testCanPassArrayObjectAsSecondArgument(): void
    {
        $model = new ArrayObject([
            'foo' => 'bar',
            'bar' => 'baz',
        ]);

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);
        $return = $this->helper->__invoke('partialVars.phtml', $model);

        foreach ($model as $key => $value) {
            $string = sprintf('%s: %s', $key, $value);
            $this->assertStringContainsString($string, $return);
        }
    }

    public function testCanPassViewModelAsSoleArgument(): void
    {
        $model = new ViewModel([
            'foo' => 'bar',
            'bar' => 'baz',
        ]);
        $model->setTemplate('partialVars.phtml');

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);
        $return = $this->helper->__invoke($model);

        foreach ($model->getVariables() as $key => $value) {
            $string = sprintf('%s: %s', $key, $value);
            $this->assertStringContainsString($string, $return);
        }
    }
}
