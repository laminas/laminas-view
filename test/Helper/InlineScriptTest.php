<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\View\Helper;
use PHPUnit\Framework\TestCase;

class InlineScriptTest extends TestCase
{
    /** @var Helper\InlineScript */
    public $helper;

    protected function setUp(): void
    {
        $this->helper = new Helper\InlineScript();
    }

    public function testInlineScriptReturnsObjectInstance(): void
    {
        $placeholder = $this->helper->__invoke();
        $this->assertInstanceOf(Helper\InlineScript::class, $placeholder);
    }
}
