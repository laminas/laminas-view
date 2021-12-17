<?php

namespace LaminasTest\View\Helper;

use Laminas\I18n\Translator\Translator;
use Laminas\View\Helper;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Laminas\View\Helper\HeadTitle.
 *
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class HeadTitleTest extends TestCase
{
    /**
     * @var Helper\HeadTitle
     */
    public $helper;

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
        $this->basePath = __DIR__ . '/_files/modules';
        $this->helper = new Helper\HeadTitle();
    }

    public function testHeadTitleReturnsObjectInstance(): void
    {
        $placeholder = $this->helper->__invoke();
        $this->assertInstanceOf(Helper\HeadTitle::class, $placeholder);
    }

    public function testCanSetTitleViaHeadTitle(): void
    {
        $placeholder = $this->helper->__invoke('Foo Bar', 'SET');
        $this->assertEquals('Foo Bar', $placeholder->renderTitle());
    }

    public function testToStringWrapsToTitleTag(): void
    {
        $placeholder = $this->helper->__invoke('Foo Bar', 'SET');
        $this->assertEquals('<title>Foo Bar</title>', $placeholder->toString());
    }

    public function testCanAppendTitleViaHeadTitle(): void
    {
        $this->helper->__invoke('Foo');
        $placeholder = $this->helper->__invoke('Bar');
        $this->assertEquals('FooBar', $placeholder->renderTitle());
    }

    public function testCanPrependTitleViaHeadTitle(): void
    {
        $this->helper->__invoke('Foo');
        $placeholder = $this->helper->__invoke('Bar', 'PREPEND');
        $this->assertEquals('BarFoo', $placeholder->renderTitle());
    }

    public function testReturnedPlaceholderRenderTitleContainsFullTitleElement(): void
    {
        $this->helper->__invoke('Foo');
        $placeholder = $this->helper->__invoke('Bar', 'APPEND')->setSeparator(' :: ');
        $this->assertEquals('Foo :: Bar', $placeholder->renderTitle());
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
        $this->assertStringContainsString('br /', $string);
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

    /**
     * @issue Laminas-2918
     *
     * @link https://getlaminas.org/issues/browse/Laminas-2918
     */
    public function testLaminas918(): void
    {
        $this->helper->__invoke('Some Title');
        $this->helper->setPrefix('Prefix: ');
        $this->helper->setPostfix(' :Postfix');

        $this->assertEquals('Prefix: Some Title :Postfix', $this->helper->renderTitle());
    }

    /**
     * @issue Laminas-3577
     *
     * @link https://getlaminas.org/issues/browse/Laminas-3577
     */
    public function testLaminas577(): void
    {
        $this->helper->setAutoEscape(true);
        $this->helper->__invoke('Some Title');
        $this->helper->setPrefix('Prefix & ');
        $this->helper->setPostfix(' & Postfix');

        $this->assertEquals('Prefix &amp; Some Title &amp; Postfix', $this->helper->renderTitle());
    }

    public function testCanTranslateTitle(): void
    {
        $loader = new TestAsset\ArrayTranslator();
        $loader->translations = [
            'Message_1' => 'Message 1 (en)',
        ];
        $translator = new Translator();
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

    /**
     * @group Laminas-8036
     */
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

    /**
     * @group Laminas-10284
     */
    public function testReturnTypeDefaultAttachOrder(): void
    {
        $this->assertInstanceOf(Helper\HeadTitle::class, $this->helper->setDefaultAttachOrder('PREPEND'));
        $this->assertEquals('PREPEND', $this->helper->getDefaultAttachOrder());
    }
}
