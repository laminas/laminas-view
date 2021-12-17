<?php

namespace LaminasTest\View\Model;

use ArrayObject;
use Laminas\View\Exception;
use Laminas\View\Model\ViewModel;
use Laminas\View\Variables as ViewVariables;
use LaminasTest\View\Model\TestAsset\Variable;
use PHPUnit\Framework\TestCase;
use stdClass;

class ViewModelTest extends TestCase
{
    public function testImplementsModelInterface(): void
    {
        $model = new ViewModel();
        $this->assertInstanceOf('Laminas\View\Model\ModelInterface', $model);
    }

    public function testImplementsClearableModelInterface(): void
    {
        $model = new ViewModel();
        $this->assertInstanceOf('Laminas\View\Model\ClearableModelInterface', $model);
    }

    public function testAllowsEmptyConstructor(): void
    {
        $model = new ViewModel();
        $this->assertInstanceOf('Laminas\View\Variables', $model->getVariables());
        $this->assertEquals([], $model->getOptions());
    }

    public function testAllowsEmptyOptionsArgumentToConstructor(): void
    {
        $model = new ViewModel(['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $model->getVariables());
        $this->assertEquals([], $model->getOptions());
    }

    public function testAllowsPassingBothVariablesAndOptionsArgumentsToConstructor(): void
    {
        $model = new ViewModel(['foo' => 'bar'], ['template' => 'foo/bar']);
        $this->assertEquals(['foo' => 'bar'], $model->getVariables());
        $this->assertEquals(['template' => 'foo/bar'], $model->getOptions());
    }

    public function testAllowsPassingTraversableArgumentsToVariablesAndOptionsInConstructor(): void
    {
        $vars    = new ArrayObject;
        $options = new ArrayObject;
        $model = new ViewModel($vars, $options);
        $this->assertSame($vars, $model->getVariables());
        $this->assertSame(iterator_to_array($options), $model->getOptions());
    }

    public function testAllowsPassingNonArrayAccessObjectsAsArrayInConstructor(): void
    {
        $vars  = ['foo' => new Variable];
        $model = new ViewModel($vars);
        $this->assertSame($vars, $model->getVariables());
    }

    public function testCanSetVariablesSingly(): void
    {
        $model = new ViewModel(['foo' => 'bar']);
        $model->setVariable('bar', 'baz');
        $this->assertEquals(['foo' => 'bar', 'bar' => 'baz'], $model->getVariables());
    }

    public function testCanOverwriteVariablesSingly(): void
    {
        $model = new ViewModel(['foo' => 'bar']);
        $model->setVariable('foo', 'baz');
        $this->assertEquals(['foo' => 'baz'], $model->getVariables());
    }

    public function testSetVariablesMergesWithPreviouslyStoredVariables(): ViewModel
    {
        $model = new ViewModel(['foo' => 'bar', 'bar' => 'baz']);
        $model->setVariables(['bar' => 'BAZBAT']);
        $this->assertEquals(['foo' => 'bar', 'bar' => 'BAZBAT'], $model->getVariables());
        return $model;
    }

    public function testCanUnsetVariable(): void
    {
        $model = new ViewModel(['foo' => 'bar']);
        $model->__unset('foo');
        $this->assertEquals([], $model->getVariables());
    }

    /**
     * @depends testSetVariablesMergesWithPreviouslyStoredVariables
     */
    public function testCanClearAllVariables(ViewModel $model): void
    {
        $model->clearVariables();
        $vars = $model->getVariables();
        $this->assertEquals(0, count($vars));
    }

    public function testCanSetOptionsSingly(): void
    {
        $model = new ViewModel([], ['foo' => 'bar']);
        $model->setOption('bar', 'baz');
        $this->assertEquals(['foo' => 'bar', 'bar' => 'baz'], $model->getOptions());
    }

    public function testCanOverwriteOptionsSingly(): void
    {
        $model = new ViewModel([], ['foo' => 'bar']);
        $model->setOption('foo', 'baz');
        $this->assertEquals(['foo' => 'baz'], $model->getOptions());
    }

    public function testSetOptionsOverwritesAllPreviouslyStored(): ViewModel
    {
        $model = new ViewModel([], ['foo' => 'bar', 'bar' => 'baz']);
        $model->setOptions(['bar' => 'BAZBAT']);
        $this->assertEquals(['bar' => 'BAZBAT'], $model->getOptions());
        return $model;
    }

    public function testOptionsAreInternallyConvertedToAnArrayFromTraversables(): void
    {
        $options = new ArrayObject(['foo' => 'bar']);
        $model = new ViewModel();
        $model->setOptions($options);
        $this->assertEquals($options->getArrayCopy(), $model->getOptions());
    }

    /**
     * @depends testSetOptionsOverwritesAllPreviouslyStored
     */
    public function testCanClearOptions(ViewModel $model): void
    {
        $model->clearOptions();
        $this->assertEquals([], $model->getOptions());
    }

    public function testPassingAnInvalidArgumentToSetVariablesRaisesAnException(): void
    {
        $model = new ViewModel();
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('expects an array');
        $model->setVariables(new stdClass);
    }

    public function testPassingAnInvalidArgumentToSetOptionsRaisesAnException(): void
    {
        $model = new ViewModel();
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('expects an array');
        $model->setOptions(new stdClass);
    }

    public function testCaptureToDefaultsToContent(): void
    {
        $model = new ViewModel();
        $this->assertEquals('content', $model->captureTo());
    }

    public function testCaptureToValueIsMutable(): void
    {
        $model = new ViewModel();
        $model->setCaptureTo('foo');
        $this->assertEquals('foo', $model->captureTo());
    }

    public function testHasNoChildrenByDefault(): void
    {
        $model = new ViewModel();
        $this->assertFalse($model->hasChildren());
    }

    public function testWhenNoChildrenCountIsZero(): void
    {
        $model = new ViewModel();
        $this->assertEquals(0, count($model));
    }

    public function testCanAddChildren(): void
    {
        $model = new ViewModel();
        $child = new ViewModel();
        $model->addChild($child);
        $this->assertTrue($model->hasChildren());
    }

    public function testCanCountChildren(): ViewModel
    {
        $model = new ViewModel();
        $child = new ViewModel();
        $model->addChild($child);
        $this->assertEquals(1, count($model));
        $model->addChild($child);
        $this->assertEquals(2, count($model));
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
            $this->assertSame($child, $childModel);
            $count++;
        }
        $this->assertEquals(3, $count);
    }

    /**
     * @depends testCanCountChildren
     */
    public function testCanClearChildren(ViewModel $model): void
    {
        $model->clearChildren();
        $this->assertEquals(0, count($model));
    }

    public function testTemplateIsEmptyByDefault(): void
    {
        $model    = new ViewModel();
        $template = $model->getTemplate();
        $this->assertEmpty($template);
    }

    public function testTemplateIsMutable(): void
    {
        $model = new ViewModel();
        $model->setTemplate('foo');
        $this->assertEquals('foo', $model->getTemplate());
    }

    public function testIsNotTerminatedByDefault(): void
    {
        $model = new ViewModel();
        $this->assertFalse($model->terminate());
    }

    public function testTerminationFlagIsMutable(): void
    {
        $model = new ViewModel();
        $model->setTerminal(true);
        $this->assertTrue($model->terminate());
    }

    public function testAddChildAllowsSpecifyingCaptureToValue(): void
    {
        $model = new ViewModel();
        $child = new ViewModel();
        $model->addChild($child, 'foo');
        $this->assertTrue($model->hasChildren());
        $this->assertEquals('foo', $child->captureTo());
    }

    public function testAllowsPassingViewVariablesContainerAsVariablesToConstructor(): void
    {
        $variables = new ViewVariables();
        $model     = new ViewModel($variables);
        $this->assertSame($variables, $model->getVariables());
    }

    public function testPassingOverwriteFlagWhenSettingVariablesOverwritesContainer(): void
    {
        $variables = new ViewVariables(['foo' => 'bar']);
        $model     = new ViewModel($variables);
        $overwrite = new ViewVariables(['foo' => 'baz']);
        $model->setVariables($overwrite, true);
        $this->assertSame($overwrite, $model->getVariables());
    }

    public function testPropertyOverloadingGivesAccessToProperties(): void
    {
        $model      = new ViewModel();
        $variables  = $model->getVariables();
        $model->foo = 'bar';
        $this->assertTrue(isset($model->foo));
        $this->assertEquals('bar', $variables['foo']);
        $this->assertEquals('bar', $model->foo);

        unset($model->foo);
        $this->assertFalse(isset($model->foo));
        $this->assertFalse(isset($variables['foo']));
    }

    public function testPropertyOverloadingAllowsWritingPropertiesAfterSetVariablesHasBeenCalled(): void
    {
        $model = new ViewModel();
        $model->setVariables(['foo' => 'bar']);
        $model->bar = 'baz';

        $this->assertTrue(isset($model->bar));
        $this->assertEquals('baz', $model->bar);
        $variables = $model->getVariables();
        $this->assertTrue(isset($variables['bar']));
        $this->assertEquals('baz', $variables['bar']);
    }

    public function testGetChildrenByCaptureTo(): void
    {
        $model = new ViewModel();
        $child = new ViewModel();
        $model->addChild($child, 'foo');

        $this->assertEquals([$child], $model->getChildrenByCaptureTo('foo'));
    }

    public function testGetChildrenByCaptureToRecursive(): void
    {
        $model = new ViewModel();
        $child = new ViewModel();
        $subChild = new ViewModel();
        $child->addChild($subChild, 'bar');
        $model->addChild($child, 'foo');

        $this->assertEquals([$subChild], $model->getChildrenByCaptureTo('bar'));
    }

    public function testGetChildrenByCaptureToNonRecursive(): void
    {
        $model = new ViewModel();
        $child = new ViewModel();
        $subChild = new ViewModel();
        $child->addChild($subChild, 'bar');
        $model->addChild($child, 'foo');

        $this->assertEmpty($model->getChildrenByCaptureTo('bar', false));
    }

    public function testCloneCopiesVariables(): void
    {
        $model1 = new ViewModel();
        $model1->setVariables(['a' => 'foo']);
        $model2 = clone $model1;
        $model2->setVariables(['a' => 'bar']);

        $this->assertEquals('foo', $model1->getVariable('a'));
        $this->assertEquals('bar', $model2->getVariable('a'));
    }

    public function testCloneWithArray(): void
    {
        $model1 = new ViewModel(['a' => 'foo']);
        $model2 = clone $model1;
        $model2->setVariables(['a' => 'bar']);

        $this->assertEquals('foo', $model1->getVariable('a'));
        $this->assertEquals('bar', $model2->getVariable('a'));
    }

    /**
     * @psalm-return array<array-key, array{
     *     0: array<string, null|string>|ArrayObject<string, null|string>,
     *     1: null|string,
     *     2: null|string
     * }>
     */
    public function variableValue(): array
    {
        return [
            // variables                     default   expected

            // if it is set always get the value
            [['foo' => 'bar'],                  'baz', 'bar'],
            [['foo' => 'bar'],                  null,  'bar'],
            [new ArrayObject(['foo' => 'bar']), 'baz', 'bar'],
            [new ArrayObject(['foo' => 'bar']), null,  'bar'],

            // if it is null always get null value
            [['foo' => null],                   null,  null],
            [['foo' => null],                   'baz', null],
            [new ArrayObject(['foo' => null]),  null,  null],
            [new ArrayObject(['foo' => null]),  'baz', null],

            // when it is not set always get default value
            [[],                                'baz', 'baz'],
            [new ArrayObject(),                 'baz', 'baz'],
        ];
    }

    /**
     * @dataProvider variableValue
     *
     * @param array|ArrayObject $variables
     * @param string|null $default
     * @param string|null $expected
     */
    public function testGetVariableSetByConstruct($variables, $default, $expected): void
    {
        $model = new ViewModel($variables);

        self::assertSame($expected, $model->getVariable('foo', $default));
    }

    /**
     * @dataProvider variableValue
     *
     * @param array|ArrayObject $variables
     * @param string|null $default
     * @param string|null $expected
     */
    public function testGetVariableSetBySetter($variables, $default, $expected): void
    {
        $model = new ViewModel();
        $model->setVariables($variables);

        self::assertSame($expected, $model->getVariable('foo', $default));
    }
}
