<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\View\Renderer\PhpRenderer;
use LaminasTest\View\Helper\TestAsset\ConcreteElementHelper;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Laminas\View\Helper\AbstractHtmlElement
 */
class AbstractHtmlElementTest extends TestCase
{
    /**
     * @var ConcreteElementHelper
     */
    protected $helper;

    protected function setUp(): void
    {
        $this->helper = new ConcreteElementHelper();
        $this->helper->setView(new PhpRenderer());
    }

    /**
     * @group #5991
     *
     * @return void
     */
    public function testWillEscapeValueAttributeValuesCorrectly(): void
    {
        self::assertEquals(
            ' data-value="breaking&#x20;your&#x20;HTML&#x20;like&#x20;a&#x20;boss&#x21;&#x20;&#x5C;"',
            $this->helper->compileAttributes(['data-value' => 'breaking your HTML like a boss! \\'])
        );
    }

    public function testThatAttributesWithANullValueArePresentedAsAnEmptyString(): void
    {
        $expect = 'something=""';
        self::assertStringContainsString($expect, $this->helper->compileAttributes(['something' => null]));
    }
}
