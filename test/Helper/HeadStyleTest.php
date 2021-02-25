<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\Helper;

use Laminas\View;
use Laminas\View\Helper;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Laminas\View\Helper\HeadStyle.
 *
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class HeadStyleTest extends TestCase
{
    /**
     * @var Helper\HeadStyle
     */
    public $helper;

    /**
     * @var string
     */
    public $basePath;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->basePath = __DIR__ . '/_files/modules';
        $this->helper = new Helper\HeadStyle();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->helper);
    }

    public function testHeadStyleReturnsObjectInstance()
    {
        $placeholder = $this->helper->__invoke();
        $this->assertInstanceOf(Helper\HeadStyle::class, $placeholder);
    }

    public function testAppendPrependAndSetThrowExceptionsWhenNonStyleValueProvided()
    {
        try {
            $this->helper->append('foo');
            $this->fail('Non-style value should not append');
        } catch (View\Exception\ExceptionInterface $e) {
            $this->addToAssertionCount(1);
        }

        try {
            $this->helper->offsetSet(5, 'foo');
            $this->fail('Non-style value should not offsetSet');
        } catch (View\Exception\ExceptionInterface $e) {
            $this->addToAssertionCount(1);
        }

        try {
            $this->helper->prepend('foo');
            $this->fail('Non-style value should not prepend');
        } catch (View\Exception\ExceptionInterface $e) {
            $this->addToAssertionCount(1);
        }

        try {
            $this->helper->set('foo');
            $this->fail('Non-style value should not set');
        } catch (View\Exception\ExceptionInterface $e) {
            $this->addToAssertionCount(1);
        }
    }

    public function testOverloadAppendStyleAppendsStyleToStack()
    {
        $string = 'a {}';
        for ($i = 0; $i < 3; ++$i) {
            $string .= PHP_EOL . 'a {}';
            $this->helper->appendStyle($string);
            $values = $this->helper->getArrayCopy();
            $this->assertEquals($i + 1, count($values));
            $item = $values[$i];

            $this->assertInstanceOf('stdClass', $item);
            $this->assertObjectHasAttribute('content', $item);
            $this->assertObjectHasAttribute('attributes', $item);
            $this->assertEquals($string, $item->content);
        }
    }

    public function testOverloadPrependStylePrependsStyleToStack()
    {
        $string = 'a {}';
        for ($i = 0; $i < 3; ++$i) {
            $string .= PHP_EOL . 'a {}';
            $this->helper->prependStyle($string);
            $values = $this->helper->getArrayCopy();
            $this->assertEquals($i + 1, count($values));
            $item = array_shift($values);

            $this->assertInstanceOf('stdClass', $item);
            $this->assertObjectHasAttribute('content', $item);
            $this->assertObjectHasAttribute('attributes', $item);
            $this->assertEquals($string, $item->content);
        }
    }

    public function testOverloadSetOversitesStack()
    {
        $string = 'a {}';
        for ($i = 0; $i < 3; ++$i) {
            $this->helper->appendStyle($string);
            $string .= PHP_EOL . 'a {}';
        }
        $this->helper->setStyle($string);
        $values = $this->helper->getArrayCopy();
        $this->assertEquals(1, count($values));
        $item = array_shift($values);

        $this->assertInstanceOf('stdClass', $item);
        $this->assertObjectHasAttribute('content', $item);
        $this->assertObjectHasAttribute('attributes', $item);
        $this->assertEquals($string, $item->content);
    }

    public function testCanBuildStyleTagsWithAttributes()
    {
        $this->helper->setStyle('a {}', [
            'lang'  => 'us_en',
            'title' => 'foo',
            'media' => 'projection',
            'dir'   => 'rtol',
            'bogus' => 'unused'
        ]);
        $value = $this->helper->getValue();

        $this->assertObjectHasAttribute('attributes', $value);
        $attributes = $value->attributes;

        $this->assertTrue(isset($attributes['lang']));
        $this->assertTrue(isset($attributes['title']));
        $this->assertTrue(isset($attributes['media']));
        $this->assertTrue(isset($attributes['dir']));
        $this->assertTrue(isset($attributes['bogus']));
        $this->assertEquals('us_en', $attributes['lang']);
        $this->assertEquals('foo', $attributes['title']);
        $this->assertEquals('projection', $attributes['media']);
        $this->assertEquals('rtol', $attributes['dir']);
        $this->assertEquals('unused', $attributes['bogus']);
    }

    public function testRenderedStyleTagsContainHtmlEscaping()
    {
        $this->helper->setStyle('a {}', [
            'lang'  => 'us_en',
            'title' => 'foo',
            'media' => 'screen',
            'dir'   => 'rtol',
            'bogus' => 'unused'
        ]);
        $value = $this->helper->toString();
        $this->assertStringContainsString('<!--' . PHP_EOL, $value);
        $this->assertStringContainsString(PHP_EOL . '-->', $value);
    }

    public function testRenderedStyleTagsContainsDefaultMedia()
    {
        $this->helper->setStyle('a {}', [
        ]);
        $value = $this->helper->toString();
        $this->assertMatchesRegularExpression('#<style [^>]*?media="screen"#', $value, $value);
    }

    /**
     * @group Laminas-8056
     */
    public function testMediaAttributeCanHaveSpaceInCommaSeparatedString()
    {
        $this->helper->appendStyle('a { }', ['media' => 'screen, projection']);
        $string = $this->helper->toString();
        $this->assertStringContainsString('media="screen,projection"', $string);
    }

    public function testHeadStyleProxiesProperly()
    {
        $style1 = 'a {}';
        $style2 = 'a {}' . PHP_EOL . 'h1 {}';
        $style3 = 'a {}' . PHP_EOL . 'h2 {}';

        $this->helper->__invoke($style1, 'SET')
                     ->__invoke($style2, 'PREPEND')
                     ->__invoke($style3, 'APPEND');
        $this->assertEquals(3, count($this->helper));
        $values = $this->helper->getArrayCopy();
        $this->assertStringContainsString($values[0]->content, $style2);
        $this->assertStringContainsString($values[1]->content, $style1);
        $this->assertStringContainsString($values[2]->content, $style3);
    }

    public function testToStyleGeneratesValidHtml()
    {
        $style1 = 'a {}';
        $style2 = 'body {}' . PHP_EOL . 'h1 {}';
        $style3 = 'div {}' . PHP_EOL . 'li {}';

        $this->helper->__invoke($style1, 'SET')
                     ->__invoke($style2, 'PREPEND')
                     ->__invoke($style3, 'APPEND');
        $html = $this->helper->toString();
        $doc  = new \DOMDocument;
        $dom  = $doc->loadHtml($html);
        $this->assertTrue($dom);

        $styles = substr_count($html, '<style type="text/css"');
        $this->assertEquals(3, $styles);
        $styles = substr_count($html, '</style>');
        $this->assertEquals(3, $styles);
        $this->assertStringContainsString($style3, $html);
        $this->assertStringContainsString($style2, $html);
        $this->assertStringContainsString($style1, $html);
    }

    public function testCapturingCapturesToObject()
    {
        $this->helper->captureStart();
        echo 'foobar';
        $this->helper->captureEnd();
        $values = $this->helper->getArrayCopy();
        $this->assertEquals(1, count($values));
        $item = array_shift($values);
        $this->assertStringContainsString('foobar', $item->content);
    }

    public function testOverloadingOffsetSetWritesToSpecifiedIndex()
    {
        $this->helper->offsetSetStyle(100, 'foobar');
        $values = $this->helper->getArrayCopy();
        $this->assertEquals(1, count($values));
        $this->assertTrue(isset($values[100]));
        $item = $values[100];
        $this->assertStringContainsString('foobar', $item->content);
    }

    public function testInvalidMethodRaisesException(): void
    {
        $this->expectException(View\Exception\BadMethodCallException::class);
        $this->expectExceptionMessage('Method "bogusMethod" does not exist');
        $this->helper->bogusMethod();
    }

    public function testTooFewArgumentsRaisesException(): void
    {
        $this->expectException(View\Exception\BadMethodCallException::class);
        $this->expectExceptionMessage('Method "appendStyle" requires minimally content for the stylesheet');
        $this->helper->appendStyle();
    }

    public function testIndentationIsHonored()
    {
        $this->helper->setIndent(4);
        $this->helper->appendStyle('
a {
    display: none;
}');
        $this->helper->appendStyle('
h1 {
    font-weight: bold
}');
        $string = $this->helper->toString();

        $scripts = substr_count($string, '    <style');
        $this->assertEquals(2, $scripts);
        $this->assertStringContainsString('    <!--', $string);
        $this->assertStringContainsString('    a {', $string);
        $this->assertStringContainsString('    h1 {', $string);
        $this->assertStringContainsString('        display', $string);
        $this->assertStringContainsString('        font-weight', $string);
        $this->assertStringContainsString('    }', $string);
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

    public function testNestedCapturingFails()
    {
        $this->helper->__invoke()->captureStart();
        echo "Captured text";
        try {
            $this->helper->__invoke()->captureStart();
            $this->helper->__invoke()->captureEnd();
            $this->fail('Nested capturing should fail');
        } catch (View\Exception\ExceptionInterface $e) {
            $this->helper->__invoke()->captureEnd();
            $this->assertStringContainsString('Cannot nest', $e->getMessage());
        }
    }

    public function testMediaAttributeAsArray()
    {
        $this->helper->setIndent(4);
        $this->helper->appendStyle('
a {
    display: none;
}', ['media' => ['screen', 'projection']]);
        $string = $this->helper->toString();

        $scripts = substr_count($string, '    <style');
        $this->assertEquals(1, $scripts);
        $this->assertStringContainsString('    <!--', $string);
        $this->assertStringContainsString('    a {', $string);
        $this->assertStringContainsString(' media="screen,projection"', $string);
    }

    public function testMediaAttributeAsCommaSeparatedString()
    {
        $this->helper->setIndent(4);
        $this->helper->appendStyle('
a {
    display: none;
}', ['media' => 'screen,projection']);
        $string = $this->helper->toString();

        $scripts = substr_count($string, '    <style');
        $this->assertEquals(1, $scripts);
        $this->assertStringContainsString('    <!--', $string);
        $this->assertStringContainsString('    a {', $string);
        $this->assertStringContainsString(' media="screen,projection"', $string);
    }

    public function testConditionalScript()
    {
        $this->helper->appendStyle('
a {
    display: none;
}', ['media' => 'screen,projection', 'conditional' => 'lt IE 7']);
        $test = $this->helper->toString();
        $this->assertStringContainsString('<!--[if lt IE 7]>', $test);
    }

    public function testConditionalScriptNoIE()
    {
        $this->helper->appendStyle('
a {
    display: none;
}', ['media' => 'screen,projection', 'conditional' => '!IE']);
        $test = $this->helper->toString();
        $this->assertStringContainsString('<!--[if !IE]><!--><', $test);
        $this->assertStringContainsString('<!--<![endif]-->', $test);
    }

    public function testConditionalScriptNoIEWidthSpace()
    {
        $this->helper->appendStyle('
a {
    display: none;
}', ['media' => 'screen,projection', 'conditional' => '! IE']);
        $test = $this->helper->toString();
        $this->assertStringContainsString('<!--[if ! IE]><!--><', $test);
        $this->assertStringContainsString('<!--<![endif]-->', $test);
    }

    /**
     * @issue Laminas-5435
     */
    public function testContainerMaintainsCorrectOrderOfItems()
    {
        $style1 = 'a {display: none;}';
        $this->helper->offsetSetStyle(10, $style1);

        $style2 = 'h1 {font-weight: bold}';
        $this->helper->offsetSetStyle(5, $style2);

        $test = $this->helper->toString();
        $expected = '<style type="text/css" media="screen">' . PHP_EOL
                  . '<!--' . PHP_EOL
                  . $style2 . PHP_EOL
                  . '-->' . PHP_EOL
                  . '</style>' . PHP_EOL
                  . '<style type="text/css" media="screen">' . PHP_EOL
                  . '<!--' . PHP_EOL
                  . $style1 . PHP_EOL
                  . '-->' . PHP_EOL
                  . '</style>';

        $this->assertEquals($expected, $test);
    }

    /**
     * @group Laminas-9532
     */
    public function testRenderConditionalCommentsShouldNotContainHtmlEscaping()
    {
        $style = 'a{display:none;}';
        $this->helper->appendStyle($style, [
            'conditional' => 'IE 8'
        ]);
        $value = $this->helper->toString();

        $this->assertStringNotContainsString('<!--' . PHP_EOL, $value);
        $this->assertStringNotContainsString(PHP_EOL . '-->', $value);
    }
}
