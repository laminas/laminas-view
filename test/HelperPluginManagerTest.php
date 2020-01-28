<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View;

use Laminas\I18n\Translator\Translator;
use Laminas\Mvc\I18n\Translator as MvcTranslator;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;

/**
 * @category   Laminas
 * @package    Laminas_View
 * @subpackage UnitTests
 * @group      Laminas_View
 */
class HelperPluginManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->helpers = new HelperPluginManager();
    }

    public function testViewIsNullByDefault()
    {
        $this->assertNull($this->helpers->getRenderer());
    }

    public function testAllowsInjectingRenderer()
    {
        $renderer = new PhpRenderer();
        $this->helpers->setRenderer($renderer);
        $this->assertSame($renderer, $this->helpers->getRenderer());
    }

    public function testInjectsRendererToHelperWhenRendererIsPresent()
    {
        $renderer = new PhpRenderer();
        $this->helpers->setRenderer($renderer);
        $helper = $this->helpers->get('doctype');
        $this->assertSame($renderer, $helper->getView());
    }

    public function testNoRendererInjectedInHelperWhenRendererIsNotPresent()
    {
        $helper = $this->helpers->get('doctype');
        $this->assertNull($helper->getView());
    }

    public function testRegisteringInvalidHelperRaisesException()
    {
        $this->setExpectedException('Laminas\View\Exception\InvalidHelperException');
        $this->helpers->setService('test', $this);
    }

    public function testLoadingInvalidHelperRaisesException()
    {
        $this->helpers->setInvokableClass('test', get_class($this));
        $this->setExpectedException('Laminas\View\Exception\InvalidHelperException');
        $this->helpers->get('test');
    }

    public function testDefinesFactoryForIdentityPlugin()
    {
        $this->assertTrue($this->helpers->has('identity'));
    }

    public function testIdentityFactoryCanInjectAuthenticationServiceIfInParentServiceManager()
    {
        $services = new ServiceManager();
        $services->setInvokableClass('Laminas\Authentication\AuthenticationService', 'Laminas\Authentication\AuthenticationService');
        $this->helpers->setServiceLocator($services);
        $identity = $this->helpers->get('identity');
        $expected = $services->get('Laminas\Authentication\AuthenticationService');
        $this->assertSame($expected, $identity->getAuthenticationService());
    }

    public function testIfHelperIsTranslatorAwareAndMvcTranslatorIsAvailableItWillInjectTheMvcTranslator()
    {
        $translator = new MvcTranslator();
        $services   = new ServiceManager();
        $services->setService('MvcTranslator', $translator);
        $this->helpers->setServiceLocator($services);

        $helper = $this->helpers->get('HeadTitle');
        $this->assertSame($translator, $helper->getTranslator());
    }

    public function testIfHelperIsTranslatorAwareAndMvcTranslatorIsUnavailableAndTranslatorIsAvailableItWillInjectTheTranslator()
    {
        $translator = new Translator();
        $services   = new ServiceManager();
        $services->setService('Translator', $translator);
        $this->helpers->setServiceLocator($services);

        $helper = $this->helpers->get('HeadTitle');
        $this->assertSame($translator, $helper->getTranslator());
    }
}
