<?php

declare(strict_types=1);

namespace LaminasTest\View\Model;

use ArrayObject;
use Laminas\View\Model\ViewModel;
use LaminasTest\View\Model\TestAsset\Variable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

use function count;

final class ViewModelTest extends TestCase
{
    public function testAllowsPassingVariablesToConstructor(): void
    {
        $model = new ViewModel(['foo' => 'bar']);
        self::assertEquals(['foo' => 'bar'], $model->getVariables());
    }

    public function testAllowsPassingTraversableArgumentsToConstructor(): void
    {
        $vars  = new ArrayObject(['bing' => 'bong']);
        $model = new ViewModel($vars);
        self::assertSame(['bing' => 'bong'], $model->getVariables());
    }

    public function testAllowsPassingNonArrayAccessObjectsAsArrayInConstructor(): void
    {
        $model = new ViewModel(new Variable(['foo' => 'bar']));
        self::assertSame(['foo' => 'bar'], $model->getVariables());
    }

    public function testCanSetVariablesSingly(): void
    {
        $model = new ViewModel(['foo' => 'bar']);
        $model->setVariable('bar', 'baz');
        self::assertEquals(['foo' => 'bar', 'bar' => 'baz'], $model->getVariables());
    }

    public function testSetVariableHasFluentReturnType(): void
    {
        $model = new ViewModel();
        self::assertSame($model, $model->setVariable('bar', 'baz'));
    }

    public function testCanOverwriteVariablesSingly(): void
    {
        $model = new ViewModel(['foo' => 'bar']);
        $model->setVariable('foo', 'baz');
        self::assertEquals(['foo' => 'baz'], $model->getVariables());
    }

    public function testSetVariablesMergesWithPreviouslyStoredVariables(): ViewModel
    {
        $model = new ViewModel(['foo' => 'bar', 'bar' => 'baz']);
        $model->setVariables(['bar' => 'BAZBAT']);
        self::assertEquals(['foo' => 'bar', 'bar' => 'BAZBAT'], $model->getVariables());
        return $model;
    }

    public function testSetVariablesHasAFluidReturnType(): void
    {
        $model = new ViewModel();
        self::assertSame($model, $model->setVariables(['foo' => 'bar']));
    }

    public function testCanUnsetVariable(): void
    {
        $model = new ViewModel(['foo' => 'bar']);
        $model->__unset('foo');
        self::assertEquals([], $model->getVariables());
    }

    #[Depends('testSetVariablesMergesWithPreviouslyStoredVariables')]
    public function testCanClearAllVariables(ViewModel $model): void
    {
        $model->clearVariables();
        self::assertSame([], $model->getVariables());
    }

    public function testClearVariablesHasFluidReturnType(): void
    {
        $model = new ViewModel();
        self::assertSame($model, $model->clearVariables());
    }

    public function testCaptureToDefaultsToContent(): void
    {
        $model = new ViewModel();
        self::assertEquals('content', $model->captureTo());
    }

    public function testCaptureToValueIsMutable(): void
    {
        $model = new ViewModel();
        $model->setCaptureTo('foo');
        self::assertEquals('foo', $model->captureTo());
    }

    public function testHasNoChildrenByDefault(): void
    {
        $model = new ViewModel();
        self::assertFalse($model->hasChildren());
    }

    public function testWhenNoChildrenCountIsZero(): void
    {
        $model = new ViewModel();
        self::assertEquals(0, count($model));
    }

    public function testCanAddChildren(): void
    {
        $model = new ViewModel();
        $child = new ViewModel();
        $model->addChild($child);
        self::assertTrue($model->hasChildren());
    }

    public function testCanCountChildren(): ViewModel
    {
        $model = new ViewModel();
        $child = new ViewModel();
        $model->addChild($child);
        self::assertEquals(1, count($model));
        $model->addChild($child);
        self::assertEquals(2, count($model));
        return $model;
    }

    public function testCanIterateChildren(): void
    {
        $model = new ViewModel();
        $child = new ViewModel();
        $model->addChild($child);
        $model->addChild($child);
        $model->addChild($child);

        $count = 0;
        foreach ($model as $childModel) {
            self::assertSame($child, $childModel);
            $count++;
        }
        self::assertEquals(3, $count);
    }

    /**
     * @depends testCanCountChildren
     */
    public function testCanClearChildren(ViewModel $model): void
    {
        $model->clearChildren();
        self::assertCount(0, $model);
    }

    public function testTemplateIsEmptyByDefault(): void
    {
        $model    = new ViewModel();
        $template = $model->getTemplate();
        self::assertEmpty($template);
    }

    public function testTemplateIsMutable(): void
    {
        $model = new ViewModel();
        $model->setTemplate('foo');
        self::assertEquals('foo', $model->getTemplate());
    }

    public function testIsNotTerminatedByDefault(): void
    {
        $model = new ViewModel();
        self::assertFalse($model->terminate());
    }

    public function testTerminationFlagIsMutable(): void
    {
        $model = new ViewModel();
        $model->setTerminal(true);
        self::assertTrue($model->terminate());
    }

    public function testAddChildAllowsSpecifyingCaptureToValue(): void
    {
        $model = new ViewModel();
        $child = new ViewModel();
        $model->addChild($child, 'foo');
        self::assertTrue($model->hasChildren());
        self::assertEquals('foo', $child->captureTo());
    }

    public function testArbitraryIterablesCanBeUsedToSeedTheModel(): void
    {
        $object = new ArrayObject([
            'foo' => 'bar',
            'baz' => 'bat',
        ]);

        $model = new ViewModel($object);

        self::assertSame('bar', $model->foo);
        self::assertSame('bat', $model->baz);
    }

    public function testPassingOverwriteFlagWhenSettingVariablesOverwritesContainer(): void
    {
        $model = new ViewModel(['foo' => 'bar']);
        $model->setVariables(['foo' => 'baz'], true);
        self::assertSame(['foo' => 'baz'], $model->getVariables());
    }

    public function testUnknownVariablesAreNotSet(): void
    {
        $model = new ViewModel();
        self::assertFalse(isset($model->foo));
        self::assertFalse($model->__isset('foo'));
    }

    public function testVariablesCanBeSetViaPropertyOverloading(): void
    {
        $model      = new ViewModel();
        $model->foo = 'bar';
        self::assertSame('bar', $model->foo);

        $model->__set('baz', 'bat');
        self::assertSame('bat', $model->__get('baz'));
    }

    public function testPropertyOverloadingAllowsWritingPropertiesAfterSetVariablesHasBeenCalled(): void
    {
        $model = new ViewModel(['foo' => 'bar']);
        self::assertSame('bar', $model->__get('foo'));

        $model->__set('foo', 'bat');
        self::assertSame('bat', $model->__get('foo'));
    }

    public function testGetChildrenByCaptureTo(): void
    {
        $model = new ViewModel();
        $child = new ViewModel();
        $model->addChild($child, 'foo');

        self::assertEquals([$child], $model->getChildrenByCaptureTo('foo'));
    }

    public function testGetChildrenByCaptureToRecursive(): void
    {
        $model    = new ViewModel();
        $child    = new ViewModel();
        $subChild = new ViewModel();
        $child->addChild($subChild, 'bar');
        $model->addChild($child, 'foo');

        self::assertEquals([$subChild], $model->getChildrenByCaptureTo('bar'));
    }

    public function testGetChildrenByCaptureToNonRecursive(): void
    {
        $model    = new ViewModel();
        $child    = new ViewModel();
        $subChild = new ViewModel();
        $child->addChild($subChild, 'bar');
        $model->addChild($child, 'foo');

        self::assertEmpty($model->getChildrenByCaptureTo('bar', false));
    }

    public function testCloneCopiesVariables(): void
    {
        $model1 = new ViewModel();
        $model1->setVariables(['a' => 'foo']);
        $model2 = clone $model1;
        $model2->setVariables(['a' => 'bar']);

        self::assertEquals('foo', $model1->getVariable('a'));
        self::assertEquals('bar', $model2->getVariable('a'));
    }

    public function testCloneWithArray(): void
    {
        $model1 = new ViewModel(['a' => 'foo']);
        $model2 = clone $model1;
        $model2->setVariables(['a' => 'bar']);

        self::assertEquals('foo', $model1->getVariable('a'));
        self::assertEquals('bar', $model2->getVariable('a'));
    }

    /**
     * @psalm-return list<array{
     *     0: iterable<string, mixed>,
     *     1: null|string,
     *     2: null|string,
     * }>
     */
    public static function variableValue(): array
    {
        /** @var ArrayObject<string, mixed> $arrayObject */
        $arrayObject = new ArrayObject(['foo' => 'bar']);

        /** @var ArrayObject<string, mixed> $emptyObject */
        $emptyObject = new ArrayObject([]);

        return [
            // variables                     default   expected

            // if it is set always get the value
            [['foo' => 'bar'], 'baz', 'bar'],
            [['foo' => 'bar'], null, 'bar'],
            [$arrayObject, 'baz', 'bar'],
            [$arrayObject, null, 'bar'],

            // if it is null always get null value
            [['foo' => null], null, null],
            [['foo' => null], 'baz', null],
            [new ArrayObject(['foo' => null]), null, null],
            [new ArrayObject(['foo' => null]), 'baz', null],

            // when it is not set always get default value
            [[], 'baz', 'baz'],
            [$emptyObject, 'baz', 'baz'],
        ];
    }

    /** @param iterable<string, mixed> $variables */
    #[DataProvider('variableValue')]
    public function testGetVariableSetByConstruct(
        iterable $variables,
        string|null $default,
        string|null $expected,
    ): void {
        $model = new ViewModel($variables);

        self::assertSame($expected, $model->getVariable('foo', $default));
    }

    /** @param iterable<string, mixed> $variables */
    #[DataProvider('variableValue')]
    public function testGetVariableSetBySetter(iterable $variables, string|null $default, string|null $expected): void
    {
        $model = new ViewModel();
        $model->setVariables($variables);

        self::assertSame($expected, $model->getVariable('foo', $default));
    }

    public function testNullIsReturnedForUnknownVariablesWhenStrictVariablesIsOff(): void
    {
        $view = new ViewModel();
        self::assertNull($view->foo);
    }

    public function testSetTerminalHasFluentInterface(): void
    {
        $model = new ViewModel();
        self::assertSame($model, $model->setTerminal(true));
    }

    public function testSetAppendHasFluentInterface(): void
    {
        $model = new ViewModel();
        self::assertSame($model, $model->setAppend(true));
    }

    public function testSetCaptureToHasFluentInterface(): void
    {
        $model = new ViewModel();
        self::assertSame($model, $model->setCaptureTo('foot'));
    }

    public function testSetTemplateHasFluentInterface(): void
    {
        $model = new ViewModel();
        self::assertSame($model, $model->setTemplate('kermit'));
    }

    public function testAddChildHasFluentInterface(): void
    {
        $model = new ViewModel();
        self::assertSame($model, $model->addChild(new ViewModel()));
    }
}
