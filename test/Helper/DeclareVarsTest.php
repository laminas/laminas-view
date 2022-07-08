<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\View\Helper\DeclareVars;
use Laminas\View\Renderer\PhpRenderer as View;
use PHPUnit\Framework\TestCase;

use function str_replace;

use const DIRECTORY_SEPARATOR;

class DeclareVarsTest extends TestCase
{
    private View $view;

    protected function setUp(): void
    {
        $view = new View();
        $base = str_replace('/', DIRECTORY_SEPARATOR, '/../_templates');
        $view->resolver()->addPath(__DIR__ . $base);
        $view->vars()->setStrictVars(true);
        $this->view = $view;
    }

    private function declareVars(): void
    {
        $helper = $this->view->plugin(DeclareVars::class);

        $helper->__invoke(
            'varName1',
            'varName2',
            [
                'varName3' => 'defaultValue',
                'varName4' => [],
            ]
        );
    }

    public function testDeclareUndeclaredVars(): void
    {
        $this->declareVars();

        $vars = $this->view->vars();
        $this->assertTrue(isset($vars->varName1));
        $this->assertTrue(isset($vars->varName2));
        $this->assertTrue(isset($vars->varName3));
        $this->assertTrue(isset($vars->varName4));

        $this->assertEquals('defaultValue', $vars->varName3);
        $this->assertEquals([], $vars->varName4);
    }

    public function testDeclareDeclaredVars(): void
    {
        $vars           = $this->view->vars();
        $vars->varName2 = 'alreadySet';
        $vars->varName3 = 'myValue';
        $vars->varName5 = 'additionalValue';

        $this->declareVars();

        $this->assertTrue(isset($vars->varName1));
        $this->assertTrue(isset($vars->varName2));
        $this->assertTrue(isset($vars->varName3));
        $this->assertTrue(isset($vars->varName4));
        $this->assertTrue(isset($vars->varName5));

        $this->assertEquals('alreadySet', $vars->varName2);
        $this->assertEquals('myValue', $vars->varName3);
        $this->assertEquals('additionalValue', $vars->varName5);
    }
}
