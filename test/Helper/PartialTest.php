<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use ArrayObject;
use Laminas\View\Helper\Partial;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplatePathStack;
use Laminas\View\Variables;
use LaminasTest\View\Helper\TestAsset\Aggregate;
use LaminasTest\View\TestHelpers;
use PHPUnit\Framework\TestCase;
use stdClass;

use function get_object_vars;
use function sprintf;

final class PartialTest extends TestCase
{
    private Partial $helper;
    private PhpRenderer $renderer;

    protected function setUp(): void
    {
        $this->renderer = new PhpRenderer();
        $resolver       = new TemplatePathStack([
            'script_paths' => [
                __DIR__ . '/_files/modules/application/views/scripts',
            ],
        ]);
        $this->renderer->setResolver($resolver);
        $this->helper = new Partial($this->renderer);
    }

    public function testPartialRendersScript(): void
    {
        $return = $this->helper->__invoke('partialOne.phtml');
        self::assertStringContainsString('This is the first test partial', $return);
    }

    public function testPartialRendersScriptWithVars(): void
    {
        $vars = $this->renderer->vars();
        self::assertInstanceOf(Variables::class, $vars);
        $vars->assign(['message' => 'This should never be read']);

        $return = $this->helper->__invoke('partialThree.phtml', ['message' => 'This message should be read']);
        self::assertStringNotContainsString('This should never be read', $return);
        self::assertStringContainsString('This message should be read', $return, $return);
    }

    public function testObjectModelWithPublicPropertiesSetsViewVariables(): void
    {
        $model      = new stdClass();
        $model->foo = 'bar';
        $model->bar = 'baz';

        $return = $this->helper->__invoke('partialVars.phtml', $model);

        foreach (get_object_vars($model) as $key => $value) {
            self::assertIsString($value);
            $string = sprintf('%s: %s', $key, $value);
            self::assertStringContainsString($string, $return);
        }
    }

    public function testObjectModelWithToArraySetsViewVariables(): void
    {
        $model = new Aggregate();

        $return = TestHelpers::expectDeprecationWithMessage(
            'Non-iterable objects implementing a `toArray`',
            fn (): string => $this->helper->__invoke('partialVars.phtml', $model),
        );

        foreach ($model->toArray() as $key => $value) {
            $string = sprintf('%s: %s', $key, $value);
            self::assertStringContainsString($string, $return);
        }
    }

    public function testPassingNoArgsReturnsHelperInstance(): void
    {
        $test = $this->helper->__invoke();
        self::assertSame($this->helper, $test);
    }

    public function testCanPassViewModelAsSecondArgument(): void
    {
        $model = new ViewModel([
            'foo' => 'bar',
            'bar' => 'baz',
        ]);

        $return = $this->helper->__invoke('partialVars.phtml', $model);

        foreach ($model->getVariables() as $key => $value) {
            $string = sprintf('%s: %s', $key, $value);
            self::assertStringContainsString($string, $return);
        }
    }

    public function testCanPassArrayObjectAsSecondArgument(): void
    {
        $model = new ArrayObject([
            'foo' => 'bar',
            'bar' => 'baz',
        ]);

        $return = $this->helper->__invoke('partialVars.phtml', $model);

        foreach ($model as $key => $value) {
            $string = sprintf('%s: %s', $key, $value);
            self::assertStringContainsString($string, $return);
        }
    }

    public function testCanPassViewModelAsSoleArgument(): void
    {
        $model = new ViewModel([
            'foo' => 'bar',
            'bar' => 'baz',
        ]);
        $model->setTemplate('partialVars.phtml');

        $return = $this->helper->__invoke($model);

        foreach ($model->getVariables() as $key => $value) {
            self::assertIsString($value);
            $string = sprintf('%s: %s', $key, $value);
            self::assertStringContainsString($string, $return);
        }
    }

    public function testObservableStateIsResetWhenRequired(): void
    {
        $this->helper->setObjectKey('foo');
        $this->helper->resetState();
        self::assertNull($this->helper->getObjectKey());
    }
}
