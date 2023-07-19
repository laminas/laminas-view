<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use DOMDocument;
use Laminas\View;
use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Helper\HeadStyle;
use PHPUnit\Framework\TestCase;
use stdClass;

use function array_shift;
use function substr_count;

use const PHP_EOL;

class HeadStyleTest extends TestCase
{
    private HeadStyle $helper;

    protected function setUp(): void
    {
        $this->helper = new HeadStyle();
    }

    public function testInvokeWithoutArgumentsReturnsSelf(): void
    {
        self::assertSame($this->helper, $this->helper->__invoke());
    }

    public function testAppendThrowsExceptionGivenNonStyleArgument(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid value passed to append');
        $this->helper->append('foo');
    }

    public function testPrependThrowsExceptionGivenNonStyleArgument(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid value passed to prepend');
        $this->helper->prepend('foo');
    }

    public function testSetThrowsExceptionGivenNonStyleArgument(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid value passed to set');
        $this->helper->set('foo');
    }

    public function testOffsetSetThrowsExceptionGivenNonStyleArgument(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid value passed to offsetSet');
        $this->helper->offsetSet(1, 'foo');
    }

    public function testOverloadAppendStyleAppendsStyleToStack(): void
    {
        $string = 'a {}';
        for ($i = 0; $i < 3; ++$i) {
            $string .= PHP_EOL . 'a {}';
            $this->helper->appendStyle($string);
            $values = $this->helper->getContainer()->getArrayCopy();
            self::assertCount($i + 1, $values);
            $item = $values[$i];

            self::assertInstanceOf(stdClass::class, $item);
            self::assertObjectHasProperty('content', $item);
            self::assertObjectHasProperty('attributes', $item);
            self::assertEquals($string, $item->content);
        }
    }

    public function testOverloadPrependStylePrependsStyleToStack(): void
    {
        $string = 'a {}';
        for ($i = 0; $i < 3; ++$i) {
            $string .= PHP_EOL . 'a {}';
            $this->helper->prependStyle($string);
            $values = $this->helper->getContainer()->getArrayCopy();
            self::assertCount($i + 1, $values);
            $item = array_shift($values);

            self::assertInstanceOf(stdClass::class, $item);
            self::assertObjectHasProperty('content', $item);
            self::assertObjectHasProperty('attributes', $item);
            self::assertEquals($string, $item->content);
        }
    }

    public function testOverloadSetOverwritesStack(): void
    {
        $string = 'a {}';
        for ($i = 0; $i < 3; ++$i) {
            $this->helper->appendStyle($string);
            $string .= PHP_EOL . 'a {}';
        }

        $this->helper->setStyle($string);
        $values = $this->helper->getContainer()->getArrayCopy();
        self::assertCount(1, $values);
        $item = array_shift($values);

        self::assertInstanceOf(stdClass::class, $item);
        self::assertObjectHasProperty('content', $item);
        self::assertObjectHasProperty('attributes', $item);
        self::assertEquals($string, $item->content);
    }

    public function testCanBuildStyleTagsWithAttributes(): void
    {
        $this->helper->setStyle('a {}', [
            'lang'  => 'us_en',
            'title' => 'foo',
            'media' => 'projection',
            'dir'   => 'rtl',
            'bogus' => 'unused',
        ]);
        $value = $this->helper->getContainer()->getValue();
        self::assertIsObject($value);
        self::assertObjectHasProperty('attributes', $value);
        $attributes = $value->attributes;

        self::assertTrue(isset($attributes['lang']));
        self::assertTrue(isset($attributes['title']));
        self::assertTrue(isset($attributes['media']));
        self::assertTrue(isset($attributes['dir']));
        self::assertTrue(isset($attributes['bogus']));
        self::assertEquals('us_en', $attributes['lang']);
        self::assertEquals('foo', $attributes['title']);
        self::assertEquals('projection', $attributes['media']);
        self::assertEquals('rtl', $attributes['dir']);
        self::assertEquals('unused', $attributes['bogus']);
    }

    public function testRenderedStyleMarkupHasExpectedOutput(): void
    {
        $this->helper->setStyle('a {}', [
            'lang'  => 'en_us',
            'title' => 'foo',
            'media' => 'screen',
            'dir'   => 'rtl',
            'bogus' => 'unused',
        ]);

        $expect = <<<HTML
            <style type="text/css" lang="en_us" title="foo" media="screen" dir="rtl">
            a {}
            </style>
            HTML;
        self::assertSame($expect, $this->helper->toString());
    }

    public function testRenderedStyleTagsContainsDefaultMedia(): void
    {
        $this->helper->setStyle('a {}', []);
        $value = $this->helper->toString();
        self::assertMatchesRegularExpression('#<style [^>]*?media="screen"#', $value, $value);
    }

    public function testMediaAttributeCanHaveSpaceInCommaSeparatedString(): void
    {
        $this->helper->appendStyle('a { }', ['media' => 'screen, projection']);
        $string = $this->helper->toString();
        self::assertStringContainsString('media="screen,&#x20;projection"', $string);
    }

    public function testMediaAttributeCanContainARegularMediaQuery(): void
    {
        $this->helper->appendStyle('a { }', ['media' => 'screen and (max-width: 100px)']);
        $string = $this->helper->toString();
        self::assertStringContainsString(
            'media="screen&#x20;and&#x20;&#x28;max-width&#x3A;&#x20;100px&#x29;"',
            $string,
        );
    }

    public function testHeadStyleProxiesProperly(): void
    {
        $style1 = 'a {}';
        $style2 = 'a {}' . PHP_EOL . 'h1 {}';
        $style3 = 'a {}' . PHP_EOL . 'h2 {}';

        $this->helper->__invoke($style1, 'SET')
                     ->__invoke($style2, 'PREPEND')
                     ->__invoke($style3, 'APPEND');
        self::assertCount(3, $this->helper);
        $values = $this->helper->getContainer()->getArrayCopy();
        self::assertIsObject($values[0]);
        self::assertIsObject($values[1]);
        self::assertIsObject($values[2]);
        self::assertIsString($values[0]->content);
        self::assertIsString($values[1]->content);
        self::assertIsString($values[2]->content);
        self::assertStringContainsString($values[0]->content, $style2);
        self::assertStringContainsString($values[1]->content, $style1);
        self::assertStringContainsString($values[2]->content, $style3);
    }

    public function testToStyleGeneratesValidHtml(): void
    {
        $style1 = 'a {}';
        $style2 = 'body {}' . PHP_EOL . 'h1 {}';
        $style3 = 'div {}' . PHP_EOL . 'li {}';

        $this->helper->__invoke($style1, 'SET')
                     ->__invoke($style2, 'PREPEND')
                     ->__invoke($style3, 'APPEND');
        $html = $this->helper->toString();
        self::assertNotEmpty($html);
        $doc = new DOMDocument();
        $dom = $doc->loadHtml($html);
        self::assertTrue($dom);

        $styles = substr_count($html, '<style type="text/css"');
        self::assertEquals(3, $styles);
        $styles = substr_count($html, '</style>');
        self::assertEquals(3, $styles);
        self::assertStringContainsString($style3, $html);
        self::assertStringContainsString($style2, $html);
        self::assertStringContainsString($style1, $html);
    }

    public function testCapturingCapturesToObject(): void
    {
        $this->helper->captureStart();
        echo 'foobar';
        $this->helper->captureEnd();
        $values = $this->helper->getContainer()->getArrayCopy();
        self::assertCount(1, $values);
        $item = array_shift($values);
        self::assertIsObject($item);
        self::assertObjectHasProperty('content', $item);
        self::assertIsString($item->content);
        self::assertStringContainsString('foobar', $item->content);
    }

    public function testOverloadingOffsetSetWritesToSpecifiedIndex(): void
    {
        $this->helper->offsetSetStyle(100, 'foobar');
        $values = $this->helper->getContainer()->getArrayCopy();
        self::assertCount(1, $values);
        self::assertTrue(isset($values[100]));
        $item = $values[100];
        self::assertIsObject($item);
        self::assertObjectHasProperty('content', $item);
        self::assertIsString($item->content);
        self::assertStringContainsString('foobar', $item->content);
    }

    public function testInvalidMethodRaisesException(): void
    {
        $this->expectException(View\Exception\BadMethodCallException::class);
        $this->expectExceptionMessage('Method "bogusMethod" does not exist');
        /** @psalm-suppress UndefinedMagicMethod */
        $this->helper->bogusMethod();
    }

    public function testTooFewArgumentsRaisesException(): void
    {
        $this->expectException(View\Exception\BadMethodCallException::class);
        $this->expectExceptionMessage('Method "appendStyle" requires minimally content for the stylesheet');
        /** @psalm-suppress TooFewArguments */
        $this->helper->appendStyle();
    }

    public function testThatEmptyStylesWillYieldAnEmptyValue(): void
    {
        $this->helper->appendStyle('', ['media' => 'screen']);
        self::assertSame('', $this->helper->toString());
    }

    public function testIndentationIsHonored(): void
    {
        $returnValue = $this->helper->setIndent(4);
        self::assertSame($this->helper, $returnValue);
        $this->helper->appendStyle(<<<CSS
            a {
                display: none;
            }
            CSS);
        $this->helper->appendStyle(<<<CSS
            h1 {
                font-weight: bold
            }
            CSS);

        $expect = <<<HTML
                <style type="text/css" media="screen">
                a {
                    display: none;
                }
                </style>
                <style type="text/css" media="screen">
                h1 {
                    font-weight: bold
                }
                </style>
            HTML;

        self::assertSame($expect, $this->helper->toString());
    }

    public function testSerialCapturingWorks(): void
    {
        $this->helper->__invoke()->captureStart();
        echo 'first capture';
        $this->helper->__invoke()->captureEnd();

        $this->helper->__invoke()->captureStart();
        echo 'second capture';
        $this->helper->__invoke()->captureEnd();

        self::assertStringContainsString('first capture', (string) $this->helper);
        self::assertStringContainsString('second capture', (string) $this->helper);
    }

    public function testNestedCapturingFails(): void
    {
        $this->helper->__invoke()->captureStart();
        echo "Captured text";
        try {
            $this->helper->__invoke()->captureStart();
            $this->helper->__invoke()->captureEnd();
            $this->fail('Nested capturing should fail');
        } catch (View\Exception\ExceptionInterface $e) {
            $this->helper->__invoke()->captureEnd();
            self::assertStringContainsString('Cannot nest', $e->getMessage());
        }
    }

    public function testMediaAttributeAsArray(): void
    {
        $this->helper->setIndent(4);
        $this->helper->appendStyle(
            <<<CSS
            a {
                display: none;
            }
            CSS,
            ['media' => ['screen', 'projection']],
        );
        $string = $this->helper->toString();

        $scripts = substr_count($string, '    <style');
        self::assertEquals(1, $scripts);
        self::assertStringContainsString('    a {', $string);
        self::assertStringContainsString(' media="screen,&#x20;projection"', $string);
    }

    public function testThatAnExceptionIsThrownIfMediaAttributeArrayContainsNonStringValues(): void
    {
        $this->helper->appendStyle(
            'a {display: none;}',
            ['media' => [0.2, []]],
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('When the media attribute is an array, the array can only contain string values');

        $this->helper->toString();
    }

    public function testMediaAttributeAsCommaSeparatedString(): void
    {
        $this->helper->setIndent(4);
        $this->helper->appendStyle(
            <<<CSS
            a {
                display: none;
            }
            CSS,
            ['media' => 'screen,projection'],
        );
        $string = $this->helper->toString();

        $scripts = substr_count($string, '    <style');
        self::assertEquals(1, $scripts);
        self::assertStringContainsString('    a {', $string);
        self::assertStringContainsString(' media="screen,projection"', $string);
    }

    public function testConditionalScript(): void
    {
        $this->helper->appendStyle('
a {
    display: none;
}', ['media' => 'screen,projection', 'conditional' => 'lt IE 7']);
        $test = $this->helper->toString();
        self::assertStringContainsString('<!--[if lt IE 7]>', $test);
    }

    public function testConditionalScriptNoIE(): void
    {
        $this->helper->appendStyle('
a {
    display: none;
}', ['media' => 'screen,projection', 'conditional' => '!IE']);
        $test = $this->helper->toString();
        self::assertStringContainsString('<!--[if !IE]><!--><', $test);
        self::assertStringContainsString('<!--<![endif]-->', $test);
    }

    public function testConditionalScriptNoIEWidthSpace(): void
    {
        $this->helper->appendStyle('
a {
    display: none;
}', ['media' => 'screen,projection', 'conditional' => '! IE']);
        $test = $this->helper->toString();
        self::assertStringContainsString('<!--[if ! IE]><!--><', $test);
        self::assertStringContainsString('<!--<![endif]-->', $test);
    }

    public function testContainerMaintainsCorrectOrderOfItems(): void
    {
        $style1 = 'a {display: none;}';
        $this->helper->offsetSetStyle(10, $style1);

        $style2 = 'h1 {font-weight: bold}';
        $this->helper->offsetSetStyle(5, $style2);

        $test     = $this->helper->toString();
        $expected = '<style type="text/css" media="screen">'
            . PHP_EOL
            . $style2
            . PHP_EOL
            . '</style>'
            . PHP_EOL
            . '<style type="text/css" media="screen">'
            . PHP_EOL
            . $style1
            . PHP_EOL
            . '</style>';

        self::assertEquals($expected, $test);
    }

    public function testRenderConditionalCommentsShouldNotContainHtmlEscaping(): void
    {
        $style = 'a{display:none;}';
        $this->helper->appendStyle($style, [
            'conditional' => 'IE 8',
        ]);
        $value = $this->helper->toString();

        self::assertStringNotContainsString('<!--' . PHP_EOL, $value);
        self::assertStringNotContainsString(PHP_EOL . '-->', $value);
    }
}
