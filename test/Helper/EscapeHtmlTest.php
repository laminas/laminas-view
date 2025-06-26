<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\Escaper\Escaper;
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\Helper\Escaper\AbstractHelper;
use LaminasTest\View\Helper\TestAsset\ToArray;
use LaminasTest\View\TestHelpers;
use PHPUnit\Framework\TestCase;

final class EscapeHtmlTest extends TestCase
{
    private EscapeHtml $helper;

    protected function setUp(): void
    {
        $this->helper = new EscapeHtml(new Escaper());
    }

    public function testHtmlIsEscaped(): void
    {
        self::assertSame('&lt;foo&gt;', $this->helper->__invoke('<foo>'));
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
            'foo' => '&lt;b&gt;bar&lt;/b&gt;',
            'baz' => [
                '&lt;em&gt;bat&lt;/em&gt;',
                'second' => [
                    '&lt;i&gt;third&lt;/i&gt;',
                ],
            ],
        ];

        self::assertEquals($expected, $this->helper->__invoke($original, AbstractHelper::RECURSE_ARRAY));
    }

    public function testWillCastObjectsToStringsBeforeEscaping(): void
    {
        $object = new TestAsset\StringableObject('<foo>');
        self::assertSame('&lt;foo&gt;', $this->helper->__invoke($object));
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

        $object = new ToArray($original);

        $expected = [
            'foo' => '&lt;b&gt;bar&lt;/b&gt;',
            'baz' => [
                '&lt;em&gt;bat&lt;/em&gt;',
                'second' => [
                    '&lt;i&gt;third&lt;/i&gt;',
                ],
            ],
        ];

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
            'foo' => '&lt;b&gt;bar&lt;/b&gt;',
            'baz' => [
                '&lt;em&gt;bat&lt;/em&gt;',
                'second' => [
                    '&lt;i&gt;third&lt;/i&gt;',
                ],
            ],
        ];

        $object = (object) $original;
        self::assertEquals($expected, $this->helper->__invoke($object, AbstractHelper::RECURSE_OBJECT));
    }
}
