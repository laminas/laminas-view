<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\View\Helper\DeclareVars;
use Laminas\View\Renderer\PhpRenderer;
use PHPUnit\Framework\TestCase;

final class DeclareVarsTest extends TestCase
{
    private PhpRenderer $view;
    private DeclareVars $helper;

    protected function setUp(): void
    {
        $this->view   = new PhpRenderer();
        $this->helper = new DeclareVars($this->view);
    }

    public function testUndeclaredVariablesAreSetOrInitialised(): void
    {
        $vars = $this->view->vars();
        self::assertFalse(isset($vars->varName1));
        self::assertFalse(isset($vars->varName2));
        self::assertFalse(isset($vars->varName3));
        self::assertFalse(isset($vars->varName4));

        $this->helper->__invoke('varName1', 'varName2', [
            'varName3' => 'defaultValue',
            'varName4' => [],
        ]);

        self::assertSame('', $vars->varName1);
        self::assertSame('', $vars->varName2);
        self::assertSame('defaultValue', $vars->varName3);
        self::assertSame([], $vars->varName4);
    }

    public function testAlreadyDeclaredVariablesAreNotModified(): void
    {
        $vars = $this->view->vars();
        $vars->assign([
            'varName1' => 'alreadySet',
            'varName2' => 'myValue',
            'varName3' => 'additionalValue',
        ]);

        $this->helper->__invoke('varName1', 'varName2', [
            'varName3' => 'Foo Bar',
        ]);

        self::assertTrue(isset($vars->varName1));
        self::assertTrue(isset($vars->varName2));
        self::assertTrue(isset($vars->varName3));

        self::assertSame('alreadySet', $vars->varName1);
        self::assertSame('myValue', $vars->varName2);
        self::assertSame('additionalValue', $vars->varName3);
    }
}
