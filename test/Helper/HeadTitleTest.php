<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\I18n\Translator\Translator;
use Laminas\View\Helper\HeadTitle;
use PHPUnit\Framework\TestCase;

class HeadTitleTest extends TestCase
{
    /** @var HeadTitle */
    public $helper;

    protected function setUp(): void
    {
        $this->helper = new HeadTitle();
    }

    public function testInvokeWithNoArgumentsReturnsSelf(): void
    {
        self::assertSame($this->helper, $this->helper->__invoke());
    }

    public function testCanSetTitleViaHeadTitle(): void
    {
        $placeholder = $this->helper->__invoke('Foo Bar', 'SET');
        self::assertEquals('Foo Bar', $placeholder->renderTitle());
    }

    public function testToStringWrapsToTitleTag(): void
    {
        $placeholder = $this->helper->__invoke('Foo Bar', 'SET');
        self::assertEquals('<title>Foo Bar</title>', $placeholder->toString());
    }

    public function testCanAppendTitleViaHeadTitle(): void
    {
        $this->helper->__invoke('Foo');
        $this->helper->__invoke('Bar');
        self::assertEquals('FooBar', $this->helper->renderTitle());
    }

    public function testCanPrependTitleViaHeadTitle(): void
    {
        $this->helper->__invoke('Foo');
        $placeholder = $this->helper->__invoke('Bar', 'PREPEND');
        self::assertEquals('BarFoo', $placeholder->renderTitle());
    }

    public function testReturnedPlaceholderRenderTitleContainsFullTitleElement(): void
    {
        $this->helper->__invoke('Foo');
        $this->helper->__invoke('Bar', 'APPEND')->setSeparator(' :: ');
        self::assertEquals('Foo :: Bar', $this->helper->renderTitle());
    }

    public function testRenderTitleEscapesEntries(): void
    {
        $this->helper->__invoke('<script type="text/javascript">alert("foo");</script>');
        $string = $this->helper->renderTitle();
        $this->assertStringNotContainsString('<script', $string);
        $this->assertStringNotContainsString('</script>', $string);
    }

    public function testRenderTitleEscapesSeparator(): void
    {
        $this->helper->__invoke('Foo')
                     ->__invoke('Bar')
                     ->setSeparator(' <br /> ');
        $string = $this->helper->renderTitle();
        $this->assertStringNotContainsString('<br />', $string);
        $this->assertStringContainsString('Foo', $string);
        $this->assertStringContainsString('Bar', $string);
        $this->assertStringContainsString('&lt;br /&gt;', $string);
    }

    public function testIndentationIsHonored(): void
    {
        $this->helper->setIndent(4);
        $this->helper->__invoke('foo');
        $string = $this->helper->toString();

        $this->assertStringContainsString('    <title>', $string);
    }

    public function testAutoEscapeIsHonored(): void
    {
        $this->helper->__invoke('Some Title &copyright;');
        $this->assertEquals('Some Title &amp;copyright;', $this->helper->renderTitle());

        $this->assertTrue($this->helper->__invoke()->getAutoEscape());
        $this->helper->__invoke()->setAutoEscape(false);
        $this->assertFalse($this->helper->__invoke()->getAutoEscape());

        $this->assertEquals('Some Title &copyright;', $this->helper->renderTitle());
    }

    public function testThatAPrefixAndPostfixCanBeApplied(): void
    {
        $this->helper->__invoke('Some Title');
        $this->helper->setPrefix('Prefix: ');
        $this->helper->setPostfix(' :Postfix');

        $this->assertEquals('Prefix: Some Title :Postfix', $this->helper->renderTitle());
    }

    public function testThatPrefixAndPostfixAreEscapedProperly(): void
    {
        $this->helper->setAutoEscape(true);
        $this->helper->__invoke('Some Title');
        $this->helper->setPrefix('Prefix & ');
        $this->helper->setPostfix(' & Postfix');

        $this->assertEquals('Prefix &amp; Some Title &amp; Postfix', $this->helper->renderTitle());
    }

    public function testCanTranslateTitle(): void
    {
        $loader               = new TestAsset\ArrayTranslator();
        $loader->translations = [
            'Message_1' => 'Message 1 (en)',
        ];
        $translator           = new Translator();
        $translator->getPluginManager()->setService('default', $loader);
        $translator->addTranslationFile('default', '');

        $this->helper->setTranslatorEnabled(true);
        $this->helper->setTranslator($translator);
        $this->helper->__invoke('Message_1');
        $this->assertEquals('Message 1 (en)', $this->helper->renderTitle());
    }

    public function testTranslatorMethods(): void
    {
        $translatorMock = $this->createMock(Translator::class);
        $this->helper->setTranslator($translatorMock, 'foo');

        $this->assertEquals($translatorMock, $this->helper->getTranslator());
        $this->assertEquals('foo', $this->helper->getTranslatorTextDomain());
        $this->assertTrue($this->helper->hasTranslator());
        $this->assertTrue($this->helper->isTranslatorEnabled());

        $this->helper->setTranslatorEnabled(false);
        $this->assertFalse($this->helper->isTranslatorEnabled());
    }

    public function testHeadTitleZero(): void
    {
        $this->helper->__invoke('0');
        $this->assertEquals('0', $this->helper->renderTitle());
    }

    public function testCanPrependTitlesUsingDefaultAttachOrder(): void
    {
        $this->helper->setDefaultAttachOrder('PREPEND');
        $this->helper->__invoke('Foo');
        $placeholder = $this->helper->__invoke('Bar');
        $this->assertEquals('BarFoo', $placeholder->renderTitle());
    }

    public function testReturnTypeDefaultAttachOrder(): void
    {
        $this->assertInstanceOf(HeadTitle::class, $this->helper->setDefaultAttachOrder('PREPEND'));
        $this->assertEquals('PREPEND', $this->helper->getDefaultAttachOrder());
    }

    public function testCommonMagicMethods(): void
    {
        $this->helper->set('a little');
        $this->helper->prepend('Mary had');
        $this->helper->append('lamb');
        $this->helper->setSeparator(' ');

        self::assertSame('<title>Mary had a little lamb</title>', (string) $this->helper);
    }
}
