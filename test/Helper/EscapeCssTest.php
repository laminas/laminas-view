<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\Helper;

use Laminas\Escaper\Escaper;
use Laminas\View\Helper\EscapeCss as EscapeHelper;
use PHPUnit\Framework\TestCase;
use stdClass;

class EscapeCssTest extends TestCase
{
    protected $supportedEncodings = [
        'iso-8859-1',   'iso8859-1',    'iso-8859-5',   'iso8859-5',
        'iso-8859-15',  'iso8859-15',   'utf-8',        'cp866',
        'ibm866',       '866',          'cp1251',       'windows-1251',
        'win-1251',     '1251',         'cp1252',       'windows-1252',
        '1252',         'koi8-r',       'koi8-ru',      'koi8r',
        'big5',         '950',          'gb2312',       '936',
        'big5-hkscs',   'shift_jis',    'sjis',         'sjis-win',
        'cp932',        '932',          'euc-jp',       'eucjp',
        'eucjp-win',    'macroman'
    ];

    protected function setUp(): void
    {
        $this->helper = new EscapeHelper;
    }

    public function testUsesUtf8EncodingByDefault()
    {
        $this->assertEquals('UTF-8', $this->helper->getEncoding());
    }

    public function testEncodingIsImmutable()
    {
        $this->expectException(\Laminas\View\Exception\InvalidArgumentException::class);
        $this->helper->setEncoding('BIG5-HKSCS');
        $this->helper->getEscaper();
        $this->helper->setEncoding('UTF-8');
    }

    public function testGetEscaperCreatesDefaultInstanceWithCorrectEncoding()
    {
        $this->helper->setEncoding('BIG5-HKSCS');
        $escaper = $this->helper->getEscaper();
        $this->assertInstanceOf(Escaper::class, $escaper);
        $this->assertEquals('big5-hkscs', $escaper->getEncoding());
    }

    public function testSettingEscaperObjectAlsoSetsEncoding()
    {
        $escaper = new Escaper('big5-hkscs');
        $this->helper->setEscaper($escaper);
        $escaper = $this->helper->getEscaper();
        $this->assertInstanceOf(Escaper::class, $escaper);
        $this->assertEquals('big5-hkscs', $escaper->getEncoding());
    }

    public function testEscapehtmlCalledOnEscaperObject()
    {
        $escaper = $this->getMockBuilder(Escaper::class)->getMock();
        $escaper->expects($this->any())->method('escapeCss');
        $this->helper->setEscaper($escaper);
        $this->helper->__invoke('foo');
    }

    public function testAllowsRecursiveEscapingOfArrays()
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
        $test = $this->helper->__invoke($original, EscapeHelper::RECURSE_ARRAY);
        $this->assertEquals($expected, $test);
    }

    public function testWillCastObjectsToStringsBeforeEscaping()
    {
        $object = new TestAsset\Stringified;
        $test = $this->helper->__invoke($object);
        $this->assertEquals(
            'LaminasTest\5C View\5C Helper\5C TestAsset\5C Stringified',
            $test
        );
    }

    public function testCanRecurseObjectImplementingToArray()
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
            'foo' => '\3C b\3E bar\3C \2F b\3E ',
            'baz' => [
                '\3C em\3E bat\3C \2F em\3E ',
                'second' => [
                    '\3C i\3E third\3C \2F i\3E ',
                ],
            ],
        ];
        $test = $this->helper->__invoke($object, EscapeHelper::RECURSE_OBJECT);
        $this->assertEquals($expected, $test);
    }

    public function testCanRecurseObjectProperties()
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
            'foo' => '\3C b\3E bar\3C \2F b\3E ',
            'baz' => [
                '\3C em\3E bat\3C \2F em\3E ',
                'second' => [
                    '\3C i\3E third\3C \2F i\3E ',
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
     */
    public function testSettingEncodingToEmptyStringShouldThrowException()
    {
        $this->expectException(\Laminas\Escaper\Exception\InvalidArgumentException::class);
        $this->helper->setEncoding('');
        $this->helper->getEscaper();
    }

    public function testSettingValidEncodingShouldNotThrowExceptions()
    {
        foreach ($this->supportedEncodings as $value) {
            $helper = new EscapeHelper;
            $helper->setEncoding($value);
            $helper->getEscaper();
        }
    }

    /**
     * All versions of PHP - when an invalid encoding is set on htmlspecialchars()
     * a warning level error is issued and escaping continues with the default encoding
     * for that PHP version. Preventing the continuation behaviour offsets display_errors
     * off in production env.
     */
    public function testSettingEncodingToInvalidValueShouldThrowException()
    {
        $this->expectException(\Laminas\Escaper\Exception\InvalidArgumentException::class);
        $this->helper->setEncoding('completely-invalid');
        $this->helper->getEscaper();
    }
}
