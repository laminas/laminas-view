<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View;

use Laminas\Config\Config;
use Laminas\View\Variables;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_View
 */
class VariablesTest extends TestCase
{
    protected function setUp(): void
    {
        $this->error = false;
        $this->vars = new Variables;
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
        $vars = new \stdClass;
        $vars->foo = 'bar';
        $vars->bar = 'baz';

        $this->vars->assign($vars);
        $this->assertEquals('bar', $this->vars['foo']);
        $this->assertEquals('baz', $this->vars['bar']);
    }

    public function testAssignCallsToArrayWhenPresentBeforeMerging(): void
    {
        $vars = [
            'foo' => 'bar',
            'bar' => 'baz',
        ];
        $config = new Config($vars);
        $this->vars->assign($config);
        $this->assertEquals('bar', $this->vars['foo']);
        $this->assertEquals('baz', $this->vars['bar']);
    }

    public function testNullIsReturnedForUndefinedVariables(): void
    {
        $this->assertNull($this->vars['foo']);
    }

    public function handleErrors($errcode, $errmsg): void
    {
        $this->error = $errmsg;
    }

    public function testRetrievingUndefinedVariableRaisesErrorWhenStrictVarsIsRequested(): void
    {
        $this->vars->setStrictVars(true);
        set_error_handler([$this, 'handleErrors'], E_USER_NOTICE);
        $this->assertNull($this->vars['foo']);
        restore_error_handler();
        $this->assertStringContainsString('does not exist', $this->error);
    }

    /**
     * @psalm-return array<array-key, string[]>
     */
    public function values(): array
    {
        return [
            ['foo', 'bar'],
            ['xss', '<tag id="foo">\'value\'</tag>'],
        ];
    }

    public function testCallingClearEmptiesObject(): void
    {
        $this->vars->assign([
            'bar' => 'baz',
            'baz' => 'foo',
        ]);
        $this->assertEquals(2, count($this->vars));
        $this->vars->clear();
        $this->assertEquals(0, count($this->vars));
    }

    public function testAllowsSpecifyingClosureValuesAndReturningTheValue(): void
    {
        $this->vars->foo = function (): string {
            return 'bar';
        };

        $this->assertEquals('bar', $this->vars->foo);
    }

    public function testAllowsSpecifyingFunctorValuesAndReturningTheValue(): void
    {
        $this->vars->foo = new TestAsset\VariableFunctor('bar');
        $this->assertEquals('bar', $this->vars->foo);
    }
}
