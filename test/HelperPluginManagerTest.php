<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\View;

use Zend\I18n\Translator\Translator;
use Zend\Mvc\I18n\Translator as MvcTranslator;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\Exception\InvalidServiceException;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Exception\InvalidHelperException;
use Zend\View\HelperPluginManager;
use Zend\View\Helper\HelperInterface;
use Zend\View\Helper\Url;
use Zend\View\Renderer\PhpRenderer;

/**
 * @group      Zend_View
 */
class HelperPluginManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->helpers = new HelperPluginManager(new ServiceManager());
    }

    /**
     * @group 43
     */
    public function testConstructorArgumentsAreOptionalUnderV2()
    {
        if (method_exists($this->helpers, 'configure')) {
            $this->markTestSkipped('zend-servicemanager v3 plugin managers require a container argument');
        }

        $helpers = new HelperPluginManager();
        $this->assertInstanceOf(HelperPluginManager::class, $helpers);
    }

    /**
     * @group 43
     */
    public function testConstructorAllowsConfigInstanceAsFirstArgumentUnderV2()
    {
        if (method_exists($this->helpers, 'configure')) {
            $this->markTestSkipped('zend-servicemanager v3 plugin managers require a container argument');
        }

        $helpers = new HelperPluginManager(new Config([]));
        $this->assertInstanceOf(HelperPluginManager::class, $helpers);
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
        $helpers = new HelperPluginManager(new ServiceManager(), ['factories' => [
            'test' => function () {
                return $this;
            },
        ]]);
        $this->setExpectedException($this->getServiceNotFoundException($helpers));
        $helpers->get('test');
    }

    public function testLoadingInvalidHelperRaisesException()
    {
        $helpers = new HelperPluginManager(new ServiceManager(), ['invokables' => [
            'test' => get_class($this),
        ]]);
        $this->setExpectedException($this->getServiceNotFoundException($helpers));
        $helpers->get('test');
    }

    public function testDefinesFactoryForIdentityPlugin()
    {
        $this->assertTrue($this->helpers->has('identity'));
    }

    public function testIdentityFactoryCanInjectAuthenticationServiceIfInParentServiceManager()
    {
        $config = new Config(['invokables' => [
            'Zend\Authentication\AuthenticationService' =>  'Zend\Authentication\AuthenticationService',
        ]]);
        $services = new ServiceManager();
        $config->configureServiceManager($services);
        $helpers  = new HelperPluginManager($services);
        $identity = $helpers->get('identity');
        $expected = $services->get('Zend\Authentication\AuthenticationService');
        $this->assertSame($expected, $identity->getAuthenticationService());
    }

    public function testIfHelperIsTranslatorAwareAndMvcTranslatorIsAvailableItWillInjectTheMvcTranslator()
    {
        if (! class_exists(PluginFlashMessenger::class)) {
            $this->markTestSkipped(
                'Skipping zend-mvc-related tests until that component is updated '
                . 'to be forwards-compatible with zend-eventmanager, zend-stdlib, '
                . 'and zend-servicemanager v3.'
            );
        }

        $translator = new MvcTranslator($this->getMock('Zend\I18n\Translator\TranslatorInterface'));
        $config = new Config(['services' => [
            'MvcTranslator' =>  $translator,
        ]]);
        $services = new ServiceManager();
        $config->configureServiceManager($services);
        $helpers = new HelperPluginManager($services);
        $helper  = $helpers->get('HeadTitle');
        $this->assertSame($translator, $helper->getTranslator());
    }

    public function testIfHelperIsTranslatorAwareAndMvcTranslatorIsUnavailableAndTranslatorIsAvailableItWillInjectTheTranslator()
    {
        if (! class_exists(PluginFlashMessenger::class)) {
            $this->markTestSkipped(
                'Skipping zend-mvc-related tests until that component is updated '
                . 'to be forwards-compatible with zend-eventmanager, zend-stdlib, '
                . 'and zend-servicemanager v3.'
            );
        }

        $translator = new Translator();
        $config = new Config(['services' => [
            'Translator' =>  $translator,
        ]]);
        $services = new ServiceManager();
        $config->configureServiceManager($services);
        $helpers = new HelperPluginManager($services);
        $helper  = $helpers->get('HeadTitle');
        $this->assertSame($translator, $helper->getTranslator());
    }

    public function testIfHelperIsTranslatorAwareAndBothMvcTranslatorAndTranslatorAreUnavailableAndTranslatorInterfaceIsAvailableItWillInjectTheTranslator()
    {
        $translator = new Translator();
        $config = new Config(['services' => [
            'Zend\I18n\Translator\TranslatorInterface' =>  $translator,
        ]]);
        $services = new ServiceManager();
        $config->configureServiceManager($services);
        $helpers = new HelperPluginManager($services);
        $helper  = $helpers->get('HeadTitle');
        $this->assertSame($translator, $helper->getTranslator());
    }

    public function testCanOverrideAFactoryViaConfigurationPassedToConstructor()
    {
        $helper  = $this->prophesize(HelperInterface::class)->reveal();
        $helpers = new HelperPluginManager(new ServiceManager());
        $config = new Config(
            [
                'factories' => [
                    Url::class => function ($container) use ($helper) {
                        return $helper;
                    },
                ]
            ]
        );
        $config->configureServiceManager($helpers);
        $this->assertSame($helper, $helpers->get('url'));
    }

    private function getServiceNotFoundException(HelperPluginManager $manager)
    {
        if (method_exists($manager, 'configure')) {
            return InvalidServiceException::class;
        }
        return InvalidHelperException::class;
    }
}
