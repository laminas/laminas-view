<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\View\Helper\EscapeJs;
use Laminas\View\Helper\Escaper\AbstractHelper;
use PHPUnit\Framework\TestCase;

final class EscapeJsTest extends TestCase
{
    private EscapeJs $helper;

    protected function setUp(): void
    {
        $this->helper = new EscapeJs();
    }

    public function testJsIsEscaped(): void
    {
        self::assertSame('\x3Cb\x3Ebar\x3C\x2Fb\x3E', $this->helper->__invoke('<b>bar</b>'));
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
            'foo' => '\x3Cb\x3Ebar\x3C\x2Fb\x3E',
            'baz' => [
                '\x3Cem\x3Ebat\x3C\x2Fem\x3E',
                'second' => [
                    '\x3Ci\x3Ethird\x3C\x2Fi\x3E',
                ],
            ],
        ];

        self::assertEquals($expected, $this->helper->__invoke($original, AbstractHelper::RECURSE_ARRAY));
    }

    public function testWillCastObjectsToStringsBeforeEscaping(): void
    {
        $object = new TestAsset\StringableObject('<b>bar</b>');
        self::assertSame(
            '\x3Cb\x3Ebar\x3C\x2Fb\x3E',
            ($this->helper)($object),
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

        $object = new TestAsset\ToArray($original);

        $expected = [
            'foo' => '\x3Cb\x3Ebar\x3C\x2Fb\x3E',
            'baz' => [
                '\x3Cem\x3Ebat\x3C\x2Fem\x3E',
                'second' => [
                    '\x3Ci\x3Ethird\x3C\x2Fi\x3E',
                ],
            ],
        ];

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
            'foo' => '\x3Cb\x3Ebar\x3C\x2Fb\x3E',
            'baz' => [
                '\x3Cem\x3Ebat\x3C\x2Fem\x3E',
                'second' => [
                    '\x3Ci\x3Ethird\x3C\x2Fi\x3E',
                ],
            ],
        ];

        $object = (object) $original;
        self::assertEquals($expected, $this->helper->__invoke($object, AbstractHelper::RECURSE_OBJECT));
    }
}
