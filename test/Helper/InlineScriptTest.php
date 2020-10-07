<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\Helper;

use Laminas\View\Helper;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Laminas\View\Helper\InlineScript.
 *
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class InlineScriptTest extends TestCase
{
    /**
     * @var Helper\InlineScript
     */
    public $helper;

    /**
     * @var string
     */
    public $basePath;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->basePath = __DIR__ . '/_files/modules';
        $this->helper = new Helper\InlineScript();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->helper);
    }

    public function testInlineScriptReturnsObjectInstance()
    {
        $placeholder = $this->helper->__invoke();
        $this->assertInstanceOf(Helper\InlineScript::class, $placeholder);
    }
}
