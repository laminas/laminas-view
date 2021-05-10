<?php

namespace LaminasTest\View\Helper;

use Laminas\Escaper\Escaper;
use Laminas\View\Helper\EscapeUrl as EscapeHelper;
use PHPUnit\Framework\TestCase;
use stdClass;

class EscapeUrlTest extends TestCase
{
    use EscaperEncodingsTrait;

    /** @var EscapeHelper */
    private $helper;

    protected function setUp(): void
    {
        $this->helper = new EscapeHelper;
    }

    public function testUsesUtf8EncodingByDefault(): void
    {
        $this->assertEquals('UTF-8', $this->helper->getEncoding());
    }

    public function testEncodingIsImmutable(): void
    {
        $this->expectException(\Laminas\View\Exception\InvalidArgumentException::class);
        $this->helper->setEncoding('BIG5-HKSCS');
        $this->helper->getEscaper();
        $this->helper->setEncoding('UTF-8');
    }

    public function testGetEscaperCreatesDefaultInstanceWithCorrectEncoding(): void
    {
        $this->helper->setEncoding('BIG5-HKSCS');
        $escaper = $this->helper->getEscaper();
        $this->assertInstanceOf(Escaper::class, $escaper);
        $this->assertEquals('big5-hkscs', $escaper->getEncoding());
    }

    public function testSettingEscaperObjectAlsoSetsEncoding(): void
    {
        $escaper = new Escaper('big5-hkscs');
        $this->helper->setEscaper($escaper);
        $escaper = $this->helper->getEscaper();
        $this->assertInstanceOf(Escaper::class, $escaper);
        $this->assertEquals('big5-hkscs', $escaper->getEncoding());
    }

    public function testEscapeUrlIsCalledOnTheEscaperObjectWhenHelperInvoked(): void
    {
        $escaper = $this->createMock(Escaper::class);
        $escaper->expects(self::once())
            ->method('escapeUrl')
            ->with('foo');
        $this->helper->setEscaper($escaper);
        ($this->helper)('foo');
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
        $test = $this->helper->__invoke($original, EscapeHelper::RECURSE_ARRAY);
        $this->assertEquals($expected, $test);
    }

    public function testWillCastObjectsToStringsBeforeEscaping(): void
    {
        $object = new TestAsset\Stringified;
        $test = $this->helper->__invoke($object);
        $this->assertEquals(
            'LaminasTest%5CView%5CHelper%5CTestAsset%5CStringified',
            $test
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
        $object = new TestAsset\ToArray();
        $object->array = $original;

        $expected = [
            'foo' => '%3Cb%3Ebar%3C%2Fb%3E',
            'baz' => [
                '%3Cem%3Ebat%3C%2Fem%3E',
                'second' => [
                    '%3Ci%3Ethird%3C%2Fi%3E',
                ],
            ],
        ];
        $test = $this->helper->__invoke($object, EscapeHelper::RECURSE_OBJECT);
        $this->assertEquals($expected, $test);
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
        $object = new stdClass();
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
        $test = $this->helper->__invoke($object, EscapeHelper::RECURSE_OBJECT);
        $this->assertEquals($expected, $test);
    }

    /**
     * PHP 5.3 instates default encoding on empty string instead of the expected
     * warning level error for htmlspecialchars() encoding param. PHP 5.4 attempts
     * to guess the encoding or take it from php.ini default_charset when an empty
     * string is set. Both are insecure behaviours.
     *
     * @return void
     */
    public function testSettingEncodingToEmptyStringShouldThrowException(): void
    {
        $this->expectException(\Laminas\Escaper\Exception\InvalidArgumentException::class);
        $this->helper->setEncoding('');
        $this->helper->getEscaper();
    }

    /** @dataProvider supportedEncodingsProvider */
    public function testSettingValidEncodingShouldNotThrowExceptions(string $encoding): void
    {
        $this->helper->setEncoding($encoding);
        self::assertEquals($encoding, $this->helper->getEncoding());
    }

    /**
     * All versions of PHP - when an invalid encoding is set on htmlspecialchars()
     * a warning level error is issued and escaping continues with the default encoding
     * for that PHP version. Preventing the continuation behaviour offsets display_errors
     * off in production env.
     *
     * @return void
     */
    public function testSettingEncodingToInvalidValueShouldThrowException(): void
    {
        $this->expectException(\Laminas\Escaper\Exception\InvalidArgumentException::class);
        $this->helper->setEncoding('completely-invalid');
        $this->helper->getEscaper();
    }
}
