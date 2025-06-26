<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\Escaper\Escaper;
use Laminas\View\Helper\Escaper\AbstractHelper;
use Laminas\View\Helper\EscapeUrl;
use LaminasTest\View\Helper\TestAsset\ToArray;
use LaminasTest\View\TestHelpers;
use PHPUnit\Framework\TestCase;
use stdClass;

final class EscapeUrlTest extends TestCase
{
    private EscapeUrl $helper;

    protected function setUp(): void
    {
        $this->helper = new EscapeUrl(new Escaper());
    }

    public function testUrlsAreCorrectlyEscaped(): void
    {
        self::assertSame('%20', $this->helper->__invoke(' '));
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
        self::assertEquals($expected, $this->helper->__invoke($original, AbstractHelper::RECURSE_ARRAY));
    }

    public function testWillCastObjectsToStringsBeforeEscaping(): void
    {
        $object = new TestAsset\StringableObject('<i>');
        self::assertSame('%3Ci%3E', $this->helper->__invoke($object));
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
            'foo' => '%3Cb%3Ebar%3C%2Fb%3E',
            'baz' => [
                '%3Cem%3Ebat%3C%2Fem%3E',
                'second' => [
                    '%3Ci%3Ethird%3C%2Fi%3E',
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

        self::assertEquals($expected, $this->helper->__invoke($object, AbstractHelper::RECURSE_OBJECT));
    }
}
