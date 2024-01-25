<?php

declare(strict_types=1);

namespace LaminasTest\View;

use ArrayObject;
use Laminas\View\Variables;
use PHPUnit\Framework\TestCase;
use stdClass;

use function assert;
use function restore_error_handler;
use function set_error_handler;

use const E_USER_NOTICE;

class VariablesTest extends TestCase
{
    private ?string $error = null;
    private Variables $vars;

    protected function setUp(): void
    {
        $this->vars = new Variables();
    }

    public function testStrictVarsAreDisabledByDefault(): void
    {
        $this->assertFalse($this->vars->isStrict());
    }

    public function testCanSetStrictFlag(): void
    {
        $this->vars->setStrictVars(true);
        $this->assertTrue($this->vars->isStrict());
    }

    public function testAssignMergesValuesWithObject(): void
    {
        $this->vars['foo'] = 'bar';
        $this->vars->assign([
            'bar' => 'baz',
            'baz' => 'foo',
        ]);
        $this->assertEquals('bar', $this->vars['foo']);
        $this->assertEquals('baz', $this->vars['bar']);
        $this->assertEquals('foo', $this->vars['baz']);
    }

    public function testAssignCastsPlainObjectToArrayBeforeMerging(): void
    {
        $vars      = new stdClass();
        $vars->foo = 'bar';
        $vars->bar = 'baz';

        $this->vars->assign($vars);
        $this->assertEquals('bar', $this->vars['foo']);
        $this->assertEquals('baz', $this->vars['bar']);
    }

    public function testAssignCastsArrayObjectToArrayWhenPresentBeforeMerging(): void
    {
        $vars   = [
            'foo' => 'bar',
            'bar' => 'baz',
        ];
        $config = new ArrayObject($vars);
        $this->vars->assign($config);
        $this->assertEquals('bar', $this->vars['foo']);
        $this->assertEquals('baz', $this->vars['bar']);
    }

    public function testAssignCallsToArrayOnObjectsWithTheMethodBeforeMerging(): void
    {
        $object = new class {
            /** @return array<string, string> */
            public function toArray(): array
            {
                return [
                    'foo' => 'bar',
                    'bar' => 'baz',
                ];
            }
        };

        $this->vars->assign($object);
        $this->assertEquals('bar', $this->vars['foo']);
        $this->assertEquals('baz', $this->vars['bar']);
    }

    public function testNullIsReturnedForUndefinedVariables(): void
    {
        $this->assertNull($this->vars['foo']);
    }

    public function testRetrievingUndefinedVariableRaisesErrorWhenStrictVarsIsRequested(): void
    {
        $this->vars->setStrictVars(true);
        $handler = function (int $code, string $message): void {
            assert($code > -1);
            $this->error = $message;
        };
        /** @psalm-suppress InvalidArgument */
        set_error_handler($handler, E_USER_NOTICE);
        $this->assertNull($this->vars['foo']);
        restore_error_handler();
        $this->assertIsString($this->error);
        $this->assertStringContainsString('does not exist', $this->error);
    }

    public function testCallingClearEmptiesObject(): void
    {
        $this->vars->assign([
            'bar' => 'baz',
            'baz' => 'foo',
        ]);
        $this->assertCount(2, $this->vars);
        $this->vars->clear();
        $this->assertCount(0, $this->vars);
    }

    public function testAllowsSpecifyingClosureValuesAndReturningTheValue(): void
    {
        /** @psalm-suppress UndefinedPropertyAssignment */
        $this->vars->foo = static fn(): string => 'bar';

        $this->assertEquals('bar', $this->vars->foo);
    }

    public function testAllowsSpecifyingFunctorValuesAndReturningTheValue(): void
    {
        /** @psalm-suppress UndefinedPropertyAssignment */
        $this->vars->foo = new TestAsset\VariableFunctor('bar');
        $this->assertEquals('bar', $this->vars->foo);
    }
}
