<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\Escaper\Escaper;
use Laminas\View\Helper\EscapeHtmlAttr;
use Laminas\View\Helper\Escaper\AbstractHelper;
use PHPUnit\Framework\TestCase;

final class EscapeHtmlAttrTest extends TestCase
{
    private EscapeHtmlAttr $helper;

    protected function setUp(): void
    {
        $this->helper = new EscapeHtmlAttr(new Escaper());
    }

    public function testAllowsRecursiveEscapingOfArrays(): void
    {
        $original = [
            'foo' => '<b>bar</b>',
            'baz' => [
                '<em>bat</em>',
                'second' => [
                    '<i>third</i>',
                ],
            ],
        ];
        $expected = [
            'foo' => '&lt;b&gt;bar&lt;&#x2F;b&gt;',
            'baz' => [
                '&lt;em&gt;bat&lt;&#x2F;em&gt;',
                'second' => [
                    '&lt;i&gt;third&lt;&#x2F;i&gt;',
                ],
            ],
        ];

        self::assertEquals($expected, $this->helper->__invoke($original, AbstractHelper::RECURSE_ARRAY));
    }

    public function testWillCastObjectsToStringsBeforeEscaping(): void
    {
        $object = new TestAsset\StringableObject('<b>bar</b>');
        self::assertSame(
            '&lt;b&gt;bar&lt;&#x2F;b&gt;',
            $this->helper->__invoke($object),
        );
    }

    public function testCanRecurseObjectImplementingToArray(): void
    {
        $original = [
            'foo' => '<b>bar</b>',
            'baz' => [
                '<em>bat</em>',
                'second' => [
                    '<i>third</i>',
                ],
            ],
        ];

        $expected = [
            'foo' => '&lt;b&gt;bar&lt;&#x2F;b&gt;',
            'baz' => [
                '&lt;em&gt;bat&lt;&#x2F;em&gt;',
                'second' => [
                    '&lt;i&gt;third&lt;&#x2F;i&gt;',
                ],
            ],
        ];

        $object = new TestAsset\ToArray($original);
        self::assertEquals($expected, $this->helper->__invoke($object, AbstractHelper::RECURSE_OBJECT));
    }

    public function testCanRecurseObjectProperties(): void
    {
        $original = [
            'foo' => '<b>bar</b>',
            'baz' => [
                '<em>bat</em>',
                'second' => [
                    '<i>third</i>',
                ],
            ],
        ];

        $expected = [
            'foo' => '&lt;b&gt;bar&lt;&#x2F;b&gt;',
            'baz' => [
                '&lt;em&gt;bat&lt;&#x2F;em&gt;',
                'second' => [
                    '&lt;i&gt;third&lt;&#x2F;i&gt;',
                ],
            ],
        ];

        $object = (object) $original;
        self::assertEquals($expected, $this->helper->__invoke($object, AbstractHelper::RECURSE_OBJECT));
    }
}
