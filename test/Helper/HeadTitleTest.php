<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\Helper;

use Laminas\I18n\Translator\Translator;
use Laminas\View\Helper;
use Laminas\View\Helper\Placeholder\Registry;

/**
 * Test class for Laminas_View_Helper_HeadTitle.
 *
 * @category   Laminas
 * @package    Laminas_View
 * @subpackage UnitTests
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class HeadTitleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Laminas_View_Helper_HeadTitle
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
    public function setUp()
    {
        Registry::unsetRegistry();
        $this->basePath = __DIR__ . '/_files/modules';
        $this->helper = new Helper\HeadTitle();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->helper);
    }

    public function testNamespaceRegisteredInPlaceholderRegistryAfterInstantiation()
    {
        $registry = Registry::getRegistry();
        if ($registry->containerExists('Laminas_View_Helper_HeadTitle')) {
            $registry->deleteContainer('Laminas_View_Helper_HeadTitle');
        }
        $this->assertFalse($registry->containerExists('Laminas_View_Helper_HeadTitle'));
        $helper = new Helper\HeadTitle();
        $this->assertTrue($registry->containerExists('Laminas_View_Helper_HeadTitle'));
    }

    public function testHeadTitleReturnsObjectInstance()
    {
        $placeholder = $this->helper->__invoke();
        $this->assertTrue($placeholder instanceof Helper\HeadTitle);
    }

    public function testCanSetTitleViaHeadTitle()
    {
        $placeholder = $this->helper->__invoke('Foo Bar', 'SET');
        $this->assertContains('Foo Bar', $placeholder->toString());
    }

    public function testCanAppendTitleViaHeadTitle()
    {
        $placeholder = $this->helper->__invoke('Foo');
        $placeholder = $this->helper->__invoke('Bar');
        $this->assertContains('FooBar', $placeholder->toString());
    }

    public function testCanPrependTitleViaHeadTitle()
    {
        $placeholder = $this->helper->__invoke('Foo');
        $placeholder = $this->helper->__invoke('Bar', 'PREPEND');
        $this->assertContains('BarFoo', $placeholder->toString());
    }

    public function testReturnedPlaceholderToStringContainsFullTitleElement()
    {
        $placeholder = $this->helper->__invoke('Foo');
        $placeholder = $this->helper->__invoke('Bar', 'APPEND')->setSeparator(' :: ');
        $this->assertEquals('<title>Foo :: Bar</title>', $placeholder->toString());
    }

    public function testToStringEscapesEntries()
    {
        $this->helper->__invoke('<script type="text/javascript">alert("foo");</script>');
        $string = $this->helper->toString();
        $this->assertNotContains('<script', $string);
        $this->assertNotContains('</script>', $string);
    }

    public function testToStringEscapesSeparator()
    {
        $this->helper->__invoke('Foo')
                     ->__invoke('Bar')
                     ->setSeparator(' <br /> ');
        $string = $this->helper->toString();
        $this->assertNotContains('<br />', $string);
        $this->assertContains('Foo', $string);
        $this->assertContains('Bar', $string);
        $this->assertContains('br /', $string);
    }

    public function testIndentationIsHonored()
    {
        $this->helper->setIndent(4);
        $this->helper->__invoke('foo');
        $string = $this->helper->toString();

        $this->assertContains('    <title>', $string);
    }

    public function testAutoEscapeIsHonored()
    {
        $this->helper->__invoke('Some Title &copyright;');
        $this->assertEquals('<title>Some Title &amp;copyright;</title>', $this->helper->toString());

        $this->assertTrue($this->helper->__invoke()->getAutoEscape());
        $this->helper->__invoke()->setAutoEscape(false);
        $this->assertFalse($this->helper->__invoke()->getAutoEscape());


        $this->assertEquals('<title>Some Title &copyright;</title>', $this->helper->toString());
    }

    /**
     * @issue Laminas-2918
     * @link https://getlaminas.org/issues/browse/Laminas-2918
     */
    public function testLaminas918()
    {
        $this->helper->__invoke('Some Title');
        $this->helper->setPrefix('Prefix: ');
        $this->helper->setPostfix(' :Postfix');

        $this->assertEquals('<title>Prefix: Some Title :Postfix</title>', $this->helper->toString());
    }

    /**
     * @issue Laminas-3577
     * @link https://getlaminas.org/issues/browse/Laminas-3577
     */
    public function testLaminas577()
    {
        $this->helper->setAutoEscape(true);
        $this->helper->__invoke('Some Title');
        $this->helper->setPrefix('Prefix & ');
        $this->helper->setPostfix(' & Postfix');

        $this->assertEquals('<title>Prefix &amp; Some Title &amp; Postfix</title>', $this->helper->toString());
    }

    public function testCanTranslateTitle()
    {
        $loader = new TestAsset\ArrayTranslator();
        $loader->translations = array(
            'Message_1' => 'Message 1 (en)',
        );
        $translator = new Translator();
        $translator->getPluginManager()->setService('default', $loader);
        $translator->addTranslationFile('default', null);

        $this->helper->setTranslatorEnabled(true);
        $this->helper->setTranslator($translator);
        $this->helper->__invoke('Message_1');
        $this->assertEquals('<title>Message 1 (en)</title>', $this->helper->toString());
    }

    public function testTranslatorMethods()
    {
        $translatorMock = $this->getMock('Laminas\I18n\Translator\Translator');
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
    public function testHeadTitleZero()
    {
        $this->helper->__invoke('0');
        $this->assertEquals('<title>0</title>', $this->helper->toString());
    }

    public function testCanPrependTitlesUsingDefaultAttachOrder()
    {
        $this->helper->setDefaultAttachOrder('PREPEND');
        $placeholder = $this->helper->__invoke('Foo');
        $placeholder = $this->helper->__invoke('Bar');
        $this->assertContains('BarFoo', $placeholder->toString());
    }


    /**
     *  @group Laminas-10284
     */
    public function testReturnTypeDefaultAttachOrder()
    {
        $this->assertTrue($this->helper->setDefaultAttachOrder('PREPEND') instanceof Helper\HeadTitle);
        $this->assertEquals('PREPEND', $this->helper->getDefaultAttachOrder());
    }
}
