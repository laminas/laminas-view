<?php

namespace LaminasTest\View\Helper;

use const PHP_EOL;

use Laminas\View\Exception;
use Laminas\View\Exception\ExceptionInterface as ViewException;
use Laminas\View\Helper;
use Laminas\View\Renderer\PhpRenderer as View;
use PHPUnit\Framework\TestCase;

use function array_shift;
use function count;
use function set_error_handler;
use function sprintf;
use function str_replace;
use function substr_count;
use function ucwords;

/**
 * Test class for Laminas\View\Helper\HeadMeta.
 *
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class HeadMetaTest extends TestCase
{
    /**
     * @var Helper\HeadMeta
     */
    public $helper;

    /**
     * @var Helper\EscapeHtmlAttr
     */
    public $attributeEscaper;

    /**
     * @var string
     */
    public $basePath;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->error = false;
        Helper\Doctype::unsetDoctypeRegistry();
        $this->basePath = __DIR__ . '/_files/modules';
        $this->view     = new View();
        $this->view->plugin('doctype')->__invoke('XHTML1_STRICT');
        $this->helper   = new Helper\HeadMeta();
        $this->helper->setView($this->view);
        $this->attributeEscaper  = new Helper\EscapeHtmlAttr();
    }

    public function handleErrors($errno, $errstr): void
    {
        $this->error = $errstr;
    }

    public function testHeadMetaReturnsObjectInstance(): void
    {
        $placeholder = $this->helper->__invoke();
        $this->assertInstanceOf(Helper\HeadMeta::class, $placeholder);
    }

    public function testThatAppendThrowsExceptionWhenNonMetaValueIsProvided(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid value passed to append');
        $this->helper->append('foo');
    }

    public function testThatOffsetSetThrowsExceptionWhenNonMetaValueIsProvided(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid value passed to offsetSet');
        $this->helper->offsetSet(3, 'foo');
    }

    public function testThatPrependThrowsExceptionWhenNonMetaValueIsProvided(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid value passed to prepend');
        $this->helper->prepend('foo');
    }

    public function testThatSetThrowsExceptionWhenNonMetaValueIsProvided(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid value passed to set');
        $this->helper->set('foo');
    }

    // @codingStandardsIgnoreStart
    protected function _inflectAction($type): string
    {
        // @codingStandardsIgnoreEnd
        $type = str_replace('-', ' ', $type);
        $type = ucwords($type);
        $type = str_replace(' ', '', $type);
        return $type;
    }

    // @codingStandardsIgnoreStart
    protected function _testOverloadAppend(string $type): void
    {
        // @codingStandardsIgnoreEnd
        $action = 'append' . $this->_inflectAction($type);
        $string = 'foo';
        for ($i = 0; $i < 3; ++$i) {
            $string .= ' foo';
            $this->helper->$action('keywords', $string);
            $values = $this->helper->getArrayCopy();
            $this->assertEquals($i + 1, count($values));

            $item   = $values[$i];
            $this->assertObjectHasAttribute('type', $item);
            $this->assertObjectHasAttribute('modifiers', $item);
            $this->assertObjectHasAttribute('content', $item);
            $this->assertObjectHasAttribute($item->type, $item);
            $this->assertEquals('keywords', $item->{$item->type});
            $this->assertEquals($string, $item->content);
        }
    }

    // @codingStandardsIgnoreStart
    protected function _testOverloadPrepend(string $type): void
    {
        // @codingStandardsIgnoreEnd
        $action = 'prepend' . $this->_inflectAction($type);
        $string = 'foo';
        for ($i = 0; $i < 3; ++$i) {
            $string .= ' foo';
            $this->helper->$action('keywords', $string);
            $values = $this->helper->getArrayCopy();
            $this->assertEquals($i + 1, count($values));
            $item = array_shift($values);

            $this->assertObjectHasAttribute('type', $item);
            $this->assertObjectHasAttribute('modifiers', $item);
            $this->assertObjectHasAttribute('content', $item);
            $this->assertObjectHasAttribute($item->type, $item);
            $this->assertEquals('keywords', $item->{$item->type});
            $this->assertEquals($string, $item->content);
        }
    }

    // @codingStandardsIgnoreStart
    protected function _testOverloadSet(string $type): void
    {
        // @codingStandardsIgnoreEnd
        $setAction = 'set' . $this->_inflectAction($type);
        $appendAction = 'append' . $this->_inflectAction($type);
        $string = 'foo';
        for ($i = 0; $i < 3; ++$i) {
            $this->helper->$appendAction('keywords', $string);
            $string .= ' foo';
        }
        $this->helper->$setAction('keywords', $string);
        $values = $this->helper->getArrayCopy();
        $this->assertEquals(1, count($values));
        $item = array_shift($values);

        $this->assertObjectHasAttribute('type', $item);
        $this->assertObjectHasAttribute('modifiers', $item);
        $this->assertObjectHasAttribute('content', $item);
        $this->assertObjectHasAttribute($item->type, $item);
        $this->assertEquals('keywords', $item->{$item->type});
        $this->assertEquals($string, $item->content);
    }

    public function testOverloadingAppendNameAppendsMetaTagToStack(): void
    {
        $this->_testOverloadAppend('name');
    }

    public function testOverloadingPrependNamePrependsMetaTagToStack(): void
    {
        $this->_testOverloadPrepend('name');
    }

    public function testOverloadingSetNameOverwritesMetaTagStack(): void
    {
        $this->_testOverloadSet('name');
    }

    public function testOverloadingAppendHttpEquivAppendsMetaTagToStack(): void
    {
        $this->_testOverloadAppend('http-equiv');
    }

    public function testOverloadingPrependHttpEquivPrependsMetaTagToStack(): void
    {
        $this->_testOverloadPrepend('http-equiv');
    }

    public function testOverloadingSetHttpEquivOverwritesMetaTagStack(): void
    {
        $this->_testOverloadSet('http-equiv');
    }

    public function testOverloadingThrowsExceptionWithFewerThanTwoArgs(): void
    {
        $this->expectException(Exception\ExceptionInterface::class);
        $this->helper->setName('foo');
    }

    public function testOverloadingThrowsExceptionWithInvalidMethodType(): void
    {
        $this->expectException(Exception\ExceptionInterface::class);
        $this->helper->setFoo('foo');
    }

    public function testCanBuildMetaTagsWithAttributes(): void
    {
        $this->helper->setName('keywords', 'foo bar', ['lang' => 'us_en', 'scheme' => 'foo', 'bogus' => 'unused']);
        $value = $this->helper->getValue();

        $this->assertObjectHasAttribute('modifiers', $value);
        $modifiers = $value->modifiers;
        $this->assertArrayHasKey('lang', $modifiers);
        $this->assertEquals('us_en', $modifiers['lang']);
        $this->assertArrayHasKey('scheme', $modifiers);
        $this->assertEquals('foo', $modifiers['scheme']);
    }

    public function testToStringReturnsValidHtml(): void
    {
        $this->helper->setName('keywords', 'foo bar', ['lang' => 'us_en', 'scheme' => 'foo', 'bogus' => 'unused'])
                     ->prependName('title', 'boo bah')
                     ->appendHttpEquiv('screen', 'projection');
        $string = $this->helper->toString();

        $metas = substr_count($string, '<meta ');
        $this->assertEquals(3, $metas);
        $metas = substr_count($string, '/>');
        $this->assertEquals(3, $metas);
        $metas = substr_count($string, 'name="');
        $this->assertEquals(2, $metas);
        $metas = substr_count($string, 'http-equiv="');
        $this->assertEquals(1, $metas);

        $attributeEscaper = $this->attributeEscaper;

        $this->assertStringContainsString('http-equiv="screen" content="projection"', $string);
        $this->assertStringContainsString('name="keywords" content="' . $attributeEscaper('foo bar') . '"', $string);
        $this->assertStringContainsString('lang="us_en"', $string);
        $this->assertStringContainsString('scheme="foo"', $string);
        $this->assertStringNotContainsString('bogus', $string);
        $this->assertStringNotContainsString('unused', $string);
        $this->assertStringContainsString('name="title" content="' . $attributeEscaper('boo bah') . '"', $string);
    }

    /**
     * @group Laminas-6637
     */
    public function testToStringWhenInvalidKeyProvidedShouldConvertThrownException(): void
    {
        $this->helper->__invoke('some-content', 'tag value', 'not allowed key');
        set_error_handler([$this, 'handleErrors']);
        $string = @$this->helper->toString();
        $this->assertEquals('', $string);
        $this->assertIsString($this->error);
    }

    public function testHeadMetaHelperCreatesItemEntry(): void
    {
        $this->helper->__invoke('foo', 'keywords');
        $values = $this->helper->getArrayCopy();
        $this->assertEquals(1, count($values));
        $item = array_shift($values);
        $this->assertEquals('foo', $item->content);
        $this->assertEquals('name', $item->type);
        $this->assertEquals('keywords', $item->name);
    }

    public function testOverloadingOffsetInsertsAtOffset(): void
    {
        $this->helper->offsetSetName(100, 'keywords', 'foo');
        $values = $this->helper->getArrayCopy();
        $this->assertEquals(1, count($values));
        $this->assertArrayHasKey(100, $values);
        $item = $values[100];
        $this->assertEquals('foo', $item->content);
        $this->assertEquals('name', $item->type);
        $this->assertEquals('keywords', $item->name);
    }

    public function testIndentationIsHonored(): void
    {
        $this->helper->setIndent(4);
        $this->helper->appendName('keywords', 'foo bar');
        $this->helper->appendName('seo', 'baz bat');
        $string = $this->helper->toString();

        $scripts = substr_count($string, '    <meta name=');
        $this->assertEquals(2, $scripts);
    }

    public function testStringRepresentationReflectsDoctype(): void
    {
        $this->view->plugin('doctype')->__invoke('HTML4_STRICT');
        $this->helper->__invoke('some content', 'foo');

        $test = $this->helper->toString();

        $attributeEscaper = $this->attributeEscaper;

        $this->assertStringNotContainsString('/>', $test);
        $this->assertStringContainsString($attributeEscaper('some content'), $test);
        $this->assertStringContainsString('foo', $test);
    }

    /**
     * @issue Laminas-2663
     */
    public function testSetNameDoesntClobber(): void
    {
        $view = new View();
        $view->plugin('headMeta')->setName('keywords', 'foo');
        $view->plugin('headMeta')->appendHttpEquiv('pragma', 'bar');
        $view->plugin('headMeta')->appendHttpEquiv('Cache-control', 'baz');
        $view->plugin('headMeta')->setName('keywords', 'bat');

        $this->assertEquals(
            '<meta http-equiv="pragma" content="bar" />' . PHP_EOL . '<meta http-equiv="Cache-control" content="baz" />'
            . PHP_EOL . '<meta name="keywords" content="bat" />',
            $view->plugin('headMeta')->toString()
        );
    }

    /**
     * @issue Laminas-2663
     */
    public function testSetNameDoesntClobberPart2(): void
    {
        $view = new View();
        $view->plugin('headMeta')->setName('keywords', 'foo');
        $view->plugin('headMeta')->setName('description', 'foo');
        $view->plugin('headMeta')->appendHttpEquiv('pragma', 'baz');
        $view->plugin('headMeta')->appendHttpEquiv('Cache-control', 'baz');
        $view->plugin('headMeta')->setName('keywords', 'bar');

        $expected = sprintf(
            '<meta name="description" content="foo" />%1$s'
            . '<meta http-equiv="pragma" content="baz" />%1$s'
            . '<meta http-equiv="Cache-control" content="baz" />%1$s'
            . '<meta name="keywords" content="bar" />',
            PHP_EOL
        );

        $this->assertEquals($expected, $view->plugin('headMeta')->toString());
    }

    /**
     * @issue Laminas-3780
     *
     * @link https://getlaminas.org/issues/browse/Laminas-3780
     */
    public function testPlacesMetaTagsInProperOrder(): void
    {
        $view = new View();
        $view->plugin('headMeta')->setName('keywords', 'foo');
        $view->plugin('headMeta')->__invoke(
            'some content',
            'bar',
            'name',
            [],
            \Laminas\View\Helper\Placeholder\Container\AbstractContainer::PREPEND
        );

        $attributeEscaper = $this->attributeEscaper;

        $expected = sprintf(
            '<meta name="bar" content="%s" />%s'
            . '<meta name="keywords" content="foo" />',
            $attributeEscaper('some content'),
            PHP_EOL
        );
        $this->assertEquals($expected, $view->plugin('headMeta')->toString());
    }

    /**
     * @issue Laminas-5435
     */
    public function testContainerMaintainsCorrectOrderOfItems(): void
    {
        $this->helper->offsetSetName(1, 'keywords', 'foo');
        $this->helper->offsetSetName(10, 'description', 'foo');
        $this->helper->offsetSetHttpEquiv(20, 'pragma', 'baz');
        $this->helper->offsetSetHttpEquiv(5, 'Cache-control', 'baz');

        $test = $this->helper->toString();

        $expected = sprintf(
            '<meta name="keywords" content="foo" />%1$s'
            . '<meta http-equiv="Cache-control" content="baz" />%1$s'
            . '<meta name="description" content="foo" />%1$s'
            . '<meta http-equiv="pragma" content="baz" />',
            PHP_EOL
        );

        $this->assertEquals($expected, $test);
    }

    /**
     * @issue Laminas-7722
     */
    public function testCharsetValidateFail(): void
    {
        $view = new View();
        $view->plugin('doctype')->__invoke('HTML4_STRICT');

        $this->expectException(Exception\ExceptionInterface::class);
        $view->plugin('headMeta')->setCharset('utf-8');
    }

    /**
     * @issue Laminas-7722
     */
    public function testCharset(): void
    {
        $view = new View();
        $view->plugin('doctype')->__invoke('HTML5');

        $view->plugin('headMeta')->setCharset('utf-8');
        $this->assertEquals(
            '<meta charset="utf-8">',
            $view->plugin('headMeta')->toString()
        );

        $view->plugin('doctype')->__invoke('XHTML5');

        $this->assertEquals(
            '<meta charset="utf-8"/>',
            $view->plugin('headMeta')->toString()
        );
    }

    public function testCharsetPosition(): void
    {
        $view = new View();
        $view->plugin('doctype')->__invoke('HTML5');

        $view->plugin('headMeta')
            ->setProperty('description', 'foobar')
            ->setCharset('utf-8');

        $this->assertEquals(
            '<meta charset="utf-8">' . PHP_EOL
            . '<meta property="description" content="foobar">',
            $view->plugin('headMeta')->toString()
        );
    }

    public function testCarsetWithXhtmlDoctypeGotException(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('XHTML* doctype has no attribute charset; please use appendHttpEquiv()');

        $view = new View();
        $view->plugin('doctype')->__invoke('XHTML1_RDFA');

        $view->plugin('headMeta')
             ->setCharset('utf-8');
    }

     /**
     * @group Laminas-9743
     */
    public function testPropertyIsSupportedWithRdfaDoctype(): void
    {
        $this->view->doctype('XHTML1_RDFA');
        $this->helper->__invoke('foo', 'og:title', 'property');

        $attributeEscaper = $this->attributeEscaper;

        $expected = sprintf('<meta property="%s" content="foo" />', $attributeEscaper('og:title'));
        $this->assertEquals($expected, $this->helper->toString());
    }

    /**
     * @group Laminas-9743
     */
    public function testPropertyIsNotSupportedByDefaultDoctype(): void
    {
        try {
            $this->helper->__invoke('foo', 'og:title', 'property');
            $this->fail('meta property attribute should not be supported on default doctype');
        } catch (ViewException $e) {
            $this->assertStringContainsString('Invalid value passed', $e->getMessage());
        }
    }

    /**
     * @group Laminas-9743
     *
     * @depends testPropertyIsSupportedWithRdfaDoctype
     */
    public function testOverloadingAppendPropertyAppendsMetaTagToStack(): void
    {
        $this->view->doctype('XHTML1_RDFA');
        $this->_testOverloadAppend('property');
    }

    /**
     * @group Laminas-9743
     *
     * @depends testPropertyIsSupportedWithRdfaDoctype
     */
    public function testOverloadingPrependPropertyPrependsMetaTagToStack(): void
    {
        $this->view->doctype('XHTML1_RDFA');
        $this->_testOverloadPrepend('property');
    }

    /**
     * @group Laminas-9743
     *
     * @depends testPropertyIsSupportedWithRdfaDoctype
     */
    public function testOverloadingSetPropertyOverwritesMetaTagStack(): void
    {
        $this->view->doctype('XHTML1_RDFA');
        $this->_testOverloadSet('property');
    }

     /**
     * @issue 3751
     */
    public function testItempropIsSupportedWithHtml5Doctype(): void
    {
        $this->view->doctype('HTML5');
        $this->helper->__invoke('HeadMeta with Microdata', 'description', 'itemprop');

        $attributeEscaper = $this->attributeEscaper;

        $expected = sprintf('<meta itemprop="description" content="%s">', $attributeEscaper('HeadMeta with Microdata'));
        $this->assertEquals($expected, $this->helper->toString());
    }

    /**
     * @issue 3751
     */
    public function testItempropIsNotSupportedByDefaultDoctype(): void
    {
        try {
            $this->helper->__invoke('HeadMeta with Microdata', 'description', 'itemprop');
            $this->fail('meta itemprop attribute should not be supported on default doctype');
        } catch (ViewException $e) {
            $this->assertStringContainsString('Invalid value passed', $e->getMessage());
        }
    }

    /**
     * @issue 3751
     *
     * @depends testItempropIsSupportedWithHtml5Doctype
     */
    public function testOverloadingAppendItempropAppendsMetaTagToStack(): void
    {
        $this->view->doctype('HTML5');
        $this->_testOverloadAppend('itemprop');
    }

    /**
     * @issue 3751
     *
     * @depends testItempropIsSupportedWithHtml5Doctype
     */
    public function testOverloadingPrependItempropPrependsMetaTagToStack(): void
    {
        $this->view->doctype('HTML5');
        $this->_testOverloadPrepend('itemprop');
    }

    /**
     * @issue 3751
     *
     * @depends testItempropIsSupportedWithHtml5Doctype
     */
    public function testOverloadingSetItempropOverwritesMetaTagStack(): void
    {
        $this->view->doctype('HTML5');
        $this->_testOverloadSet('itemprop');
    }

    /**
     * @group Laminas-11835
     */
    public function testConditional(): void
    {
        $html = $this->helper->appendHttpEquiv('foo', 'bar', ['conditional' => 'lt IE 7'])->toString();

        $this->assertMatchesRegularExpression("|^<!--\[if lt IE 7\]>|", $html);
        $this->assertMatchesRegularExpression("|<!\[endif\]-->$|", $html);
    }

    public function testConditionalNoIE(): void
    {
        $html = $this->helper->appendHttpEquiv('foo', 'bar', ['conditional' => '!IE'])->toString();

        $this->assertStringContainsString('<!--[if !IE]><!--><', $html);
        $this->assertStringContainsString('<!--<![endif]-->', $html);
    }

    public function testConditionalNoIEWidthSpace(): void
    {
        $html = $this->helper->appendHttpEquiv('foo', 'bar', ['conditional' => '! IE'])->toString();

        $this->assertStringContainsString('<!--[if ! IE]><!--><', $html);
        $this->assertStringContainsString('<!--<![endif]-->', $html);
    }

    public function testTurnOffAutoEscapeDoesNotEncode(): void
    {
        $this->helper->setAutoEscape(false)->appendHttpEquiv('foo', 'bar=baz');
        $this->assertEquals('<meta http-equiv="foo" content="bar=baz" />', $this->helper->toString());
    }
}
