<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\Escaper\Escaper;
use Laminas\View\Helper\HtmlAttributes;
use PHPUnit\Framework\TestCase;

class HtmlAttributesTest extends TestCase
{
    private HtmlAttributes $helper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->helper = new HtmlAttributes(new Escaper());
    }

    public function testThatInvokeWillReturnAttributeSetWithTheExpectedValues(): void
    {
        $set = ($this->helper)([
            'some' => 'value',
        ]);

        self::assertEquals(['some' => 'value'], $set->getArrayCopy());
    }

    public function testThatAttributeValuesWillBeEscaped(): void
    {
        $result = (string) ($this->helper)(['attribute' => '1&2']);
        self::assertStringContainsString('attribute="1&amp;2"', $result);
    }

    public function testThatAttributeKeysWillBeEscaped(): void
    {
        $result = (string) ($this->helper)(['donkeys&goats' => 'value']);
        self::assertStringContainsString('donkeys&amp;goats="value"', $result);
    }
}
