<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\Escaper\Escaper;
use Laminas\View\Helper\Doctype;
use Laminas\View\Helper\InlineScript;
use PHPUnit\Framework\TestCase;

final class InlineScriptTest extends TestCase
{
    private InlineScript $helper;

    protected function setUp(): void
    {
        $this->helper = new InlineScript(
            new Escaper(),
            new Doctype(),
        );
    }

    public function testInvokeReturnsSelf(): void
    {
        self::assertSame($this->helper, $this->helper->__invoke());
    }

    public function testBasicOperation(): void
    {
        $this->helper->appendFile('/some.js');
        $expect = <<<HTML
            <script src="&#x2F;some.js"></script>
            HTML;
        self::assertSame($expect, $this->helper->__toString());
    }
}
