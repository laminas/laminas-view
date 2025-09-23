<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\Escaper\Escaper;
use Laminas\View\Helper\EscapeHtmlAttr;
use Laminas\View\Helper\Escaper\AbstractHelper;
use LaminasTest\View\Helper\TestAsset\ToArray;
use LaminasTest\View\TestHelpers;
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

    /** @link ToArray::toArray() */
    public function testCanRecurseObjectImplementingToArrayWithDeprecation(): void
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

        $object = new ToArray($original);

        /** @var mixed $result */
        $result = TestHelpers::expectDeprecationWithMessage(
            'Non-iterable objects implementing a `toArray`',
            fn (): mixed => $this->helper->__invoke($object, AbstractHelper::RECURSE_OBJECT),
        );

        self::assertIsArray($result);
        self::assertEquals($expected, $result);
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
