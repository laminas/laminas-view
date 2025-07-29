<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\Escaper\Escaper;
use Laminas\View\Helper\EscapeCss;
use Laminas\View\Helper\Escaper\AbstractHelper;
use LaminasTest\View\Helper\TestAsset\ToArray;
use LaminasTest\View\TestHelpers;
use PHPUnit\Framework\TestCase;

final class EscapeCssTest extends TestCase
{
    private EscapeCss $helper;

    protected function setUp(): void
    {
        $this->helper = new EscapeCss(new Escaper());
    }

    public function testBasicEscape(): void
    {
        self::assertSame(
            '\3C b\3E bar\3C \2F b\3E ',
            $this->helper->__invoke('<b>bar</b>'),
        );
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
            'foo' => '\3C b\3E bar\3C \2F b\3E ',
            'baz' => [
                '\3C em\3E bat\3C \2F em\3E ',
                'second' => [
                    '\3C i\3E third\3C \2F i\3E ',
                ],
            ],
        ];

        self::assertEquals($expected, $this->helper->__invoke($original, AbstractHelper::RECURSE_ARRAY));
    }

    public function testWillCastObjectsToStringsBeforeEscaping(): void
    {
        $object = new TestAsset\StringableObject('<b>bar</b>');
        self::assertSame(
            '\3C b\3E bar\3C \2F b\3E ',
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
            'foo' => '\3C b\3E bar\3C \2F b\3E ',
            'baz' => [
                '\3C em\3E bat\3C \2F em\3E ',
                'second' => [
                    '\3C i\3E third\3C \2F i\3E ',
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
            'foo' => '\3C b\3E bar\3C \2F b\3E ',
            'baz' => [
                '\3C em\3E bat\3C \2F em\3E ',
                'second' => [
                    '\3C i\3E third\3C \2F i\3E ',
                ],
            ],
        ];

        $object = (object) $original;
        self::assertEquals($expected, $this->helper->__invoke($object, AbstractHelper::RECURSE_OBJECT));
    }
}
