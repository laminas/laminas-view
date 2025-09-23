<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use ArrayObject;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Helper\Partial;
use Laminas\View\HelperPluginManager;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplatePathStack;
use LaminasTest\View\Helper\TestAsset\Aggregate;
use LaminasTest\View\TestHelpers;
use PHPUnit\Framework\TestCase;
use stdClass;

final class PartialTest extends TestCase
{
    private Partial $helper;
    private PhpRenderer $renderer;

    protected function setUp(): void
    {
        $resolver       = new TemplatePathStack([
            'script_paths' => [
                __DIR__ . '/partial-templates',
            ],
        ]);
        $this->renderer = new PhpRenderer(new HelperPluginManager(new ServiceManager()), $resolver);
        $this->helper   = new Partial($this->renderer);
    }

    public function testPartialRendersScript(): void
    {
        $return = $this->helper->__invoke('static-content');
        self::assertStringContainsString('<p>Static Content</p>', $return);
    }

    public function testPartialRendersScriptWithVars(): void
    {
        self::assertStringContainsString(
            '<p>Expect a message</p>',
            $this->helper->__invoke('basic-variable.phtml', ['message' => 'Expect a message']),
        );

        self::assertStringContainsString(
            '<p>Kermit</p>',
            $this->helper->__invoke('basic-variable.phtml', ['message' => 'Kermit']),
        );
    }

    public function testObjectModelWithPublicPropertiesSetsViewVariables(): void
    {
        $model      = new stdClass();
        $model->foo = 'bar';
        $model->bar = 'baz';

        $return = $this->helper->__invoke('iterate-over-variables.phtml', $model);

        self::assertStringContainsString('<p>foo: bar</p>', $return);
        self::assertStringContainsString('<p>bar: baz</p>', $return);
    }

    public function testObjectModelWithToArraySetsViewVariables(): void
    {
        $model = new Aggregate();

        $return = TestHelpers::expectDeprecationWithMessage(
            'Non-iterable objects implementing a `toArray`',
            fn (): string => $this->helper->__invoke('iterate-over-variables.phtml', $model),
        );

        self::assertStringContainsString('<p>foo: bar</p>', $return);
        self::assertStringContainsString('<p>bar: baz</p>', $return);
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

        $return = $this->helper->__invoke('iterate-over-variables.phtml', $model);

        self::assertStringContainsString('<p>foo: bar</p>', $return);
        self::assertStringContainsString('<p>bar: baz</p>', $return);
    }

    public function testCanPassArrayObjectAsSecondArgument(): void
    {
        $model = new ArrayObject([
            'foo' => 'bar',
            'bar' => 'baz',
        ]);

        $return = $this->helper->__invoke('iterate-over-variables.phtml', $model);

        self::assertStringContainsString('<p>foo: bar</p>', $return);
        self::assertStringContainsString('<p>bar: baz</p>', $return);
    }

    public function testCanPassViewModelAsSoleArgument(): void
    {
        $model = new ViewModel([
            'foo' => 'bar',
            'bar' => 'baz',
        ]);
        $model->setTemplate('iterate-over-variables.phtml');

        $return = $this->helper->__invoke($model);

        self::assertStringContainsString('<p>foo: bar</p>', $return);
        self::assertStringContainsString('<p>bar: baz</p>', $return);
    }

    public function testObservableStateIsResetWhenRequired(): void
    {
        $this->helper->setObjectKey('foo');
        $this->helper->resetState();
        self::assertNull($this->helper->getObjectKey());
    }
}
