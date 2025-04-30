<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\View\Helper\Escaper\AbstractHelper;
use Laminas\View\Helper\EscapeUrl;
use PHPUnit\Framework\TestCase;
use stdClass;

final class EscapeUrlTest extends TestCase
{
    public function testUrlsAreCorrectlyEscaped(): void
    {
        $helper = new EscapeUrl();
        self::assertSame('%20', $helper->__invoke(' '));
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
            'foo' => '%3Cb%3Ebar%3C%2Fb%3E',
            'baz' => [
                '%3Cem%3Ebat%3C%2Fem%3E',
                'second' => [
                    '%3Ci%3Ethird%3C%2Fi%3E',
                ],
            ],
        ];
        $helper   = new EscapeUrl();
        self::assertEquals($expected, $helper->__invoke($original, AbstractHelper::RECURSE_ARRAY));
    }

    public function testWillCastObjectsToStringsBeforeEscaping(): void
    {
        $object = new TestAsset\StringableObject('<i>');
        $helper = new EscapeUrl();
        self::assertSame('%3Ci%3E', $helper($object));
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
        $object   = new TestAsset\ToArray($original);
        $expected = [
            'foo' => '%3Cb%3Ebar%3C%2Fb%3E',
            'baz' => [
                '%3Cem%3Ebat%3C%2Fem%3E',
                'second' => [
                    '%3Ci%3Ethird%3C%2Fi%3E',
                ],
            ],
        ];
        $helper   = new EscapeUrl();
        self::assertEquals($expected, $helper($object, AbstractHelper::RECURSE_OBJECT));
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
        $object   = new stdClass();
        foreach ($original as $key => $value) {
            $object->$key = $value;
        }

        $expected = [
            'foo' => '%3Cb%3Ebar%3C%2Fb%3E',
            'baz' => [
                '%3Cem%3Ebat%3C%2Fem%3E',
                'second' => [
                    '%3Ci%3Ethird%3C%2Fi%3E',
                ],
            ],
        ];

        $helper = new EscapeUrl();
        self::assertEquals($expected, $helper($object, AbstractHelper::RECURSE_OBJECT));
    }
}
