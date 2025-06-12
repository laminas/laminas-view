<?php

declare(strict_types=1);

namespace LaminasTest\View;

use Laminas\View\Exception\UndefinedVariableException;
use Laminas\View\Variables;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

use function iterator_to_array;

final class VariablesTest extends TestCase
{
    public function testStrictVarsAreEnabledByDefault(): void
    {
        $vars = new Variables([], true);
        $this->expectException(UndefinedVariableException::class);
        $vars->foo;
    }

    public function testStrictVariablesCanBeDisabled(): void
    {
        $vars = new Variables([], false);

        self::assertNull($vars->foo);
    }

    public function testVariablesProvidedToTheConstructorAreAvailable(): Variables
    {
        $vars = new Variables([
            'foo' => 'bar',
        ], false);

        self::assertSame('bar', $vars->foo);
        self::assertNull($vars->bar);

        return $vars;
    }

    #[Depends('testVariablesProvidedToTheConstructorAreAvailable')]
    public function testValuesCanBeAddedViaAssign(Variables $variables): Variables
    {
        $variables->assign([
            'bar' => 'foo',
        ]);

        self::assertSame('bar', $variables->foo);
        self::assertSame('foo', $variables->bar);

        return $variables;
    }

    #[Depends('testValuesCanBeAddedViaAssign')]
    public function testVariablesGivenToAssignOverwriteExistingVariables(Variables $variables): void
    {
        $variables->assign([
            'foo' => 1,
            'bar' => 2,
        ]);

        self::assertSame(1, $variables->foo);
        self::assertSame(2, $variables->bar);
    }

    public function testVariablesCanBeSetAndReadWithMagicSettersAndGetters(): void
    {
        $variables      = new Variables();
        $variables->baz = 'bat';

        self::assertSame('bat', $variables->baz);
    }

    public function testVariablesCanBeSetAndReadViaArrayAccess(): void
    {
        $variables = new Variables();
        self::assertFalse(isset($variables['foo']));
        $variables['foo'] = 'bar';
        self::assertSame('bar', $variables['foo']);
    }

    public function testVariablesIsAnIterator(): void
    {
        $data      = [
            'muppet' => 'Kermit',
        ];
        $variables = new Variables($data);

        self::assertSame($data, iterator_to_array($variables));
    }

    public function testPropertiesCanBeUnsetViaArrayAccess(): void
    {
        $variables = new Variables(['foo' => 'bar'], false);
        unset($variables['foo']);

        self::assertNull($variables->foo);
    }

    public function testPropertiesCanBeUnsetViaPropertyOverloading(): void
    {
        $variables = new Variables(['foo' => 'bar'], false);
        unset($variables->foo);

        self::assertNull($variables->foo);
    }

    public function testGetArrayCopyReturnsExpectedData(): void
    {
        $data      = [
            'muppet' => 'Kermit',
        ];
        $variables = new Variables($data);

        self::assertSame($data, $variables->getArrayCopy());
    }

    public function testVariablesAreCountable(): void
    {
        $data = [
            'a' => 1,
            'b' => 1,
            'c' => 1,
            'd' => 1,
        ];

        $variables = new Variables($data);
        self::assertCount(4, $variables);

        unset($variables['a']);
        self::assertCount(3, $variables);
    }
}
