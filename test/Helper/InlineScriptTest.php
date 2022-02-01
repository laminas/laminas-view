<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\View\Helper;
use PHPUnit\Framework\TestCase;

class InlineScriptTest extends TestCase
{
    /** @var Helper\InlineScript */
    public $helper;

    /** @var string */
    public $basePath;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->basePath = __DIR__ . '/_files/modules';
        $this->helper   = new Helper\InlineScript();
    }

    public function testInlineScriptReturnsObjectInstance(): void
    {
        $placeholder = $this->helper->__invoke();
        $this->assertInstanceOf(Helper\InlineScript::class, $placeholder);
    }
}
