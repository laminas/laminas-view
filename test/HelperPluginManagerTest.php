<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View;

use Laminas\Authentication\AuthenticationService;
use Laminas\I18n\Translator\Translator;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\Mvc\I18n\Translator as MvcTranslator;
use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Exception\InvalidHelperException;
use Laminas\View\Helper\HeadTitle;
use Laminas\View\Helper\HelperInterface;
use Laminas\View\Helper\Url;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_View
 */
class HelperPluginManagerTest extends TestCase
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
            $this->markTestSkipped('laminas-servicemanager v3 plugin managers require a container argument');
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
            $this->markTestSkipped('laminas-servicemanager v3 plugin managers require a container argument');
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
        $this->expectException($this->getServiceNotFoundException($helpers));
        $helpers->get('test');
    }

    public function testLoadingInvalidHelperRaisesException()
    {
        $helpers = new HelperPluginManager(new ServiceManager(), ['invokables' => [
            'test' => get_class($this),
        ]]);
        $this->expectException($this->getServiceNotFoundException($helpers));
        $helpers->get('test');
    }

    public function testDefinesFactoryForIdentityPlugin()
    {
        $this->assertTrue($this->helpers->has('identity'));
    }

    public function testIdentityFactoryCanInjectAuthenticationServiceIfInParentServiceManager()
    {
        $config = new Config(['invokables' => [
            AuthenticationService::class => AuthenticationService::class,
        ]]);
        $services = new ServiceManager();
        $config->configureServiceManager($services);
        $helpers  = new HelperPluginManager($services);
        $identity = $helpers->get('identity');
        $expected = $services->get(AuthenticationService::class);
        $this->assertSame($expected, $identity->getAuthenticationService());
    }

    public function testIfHelperIsTranslatorAwareAndMvcTranslatorIsAvailableItWillInjectTheMvcTranslator()
    {
        $translator = new MvcTranslator(
            $this->getMockBuilder(TranslatorInterface::class)->getMock()
        );
        $config = new Config(['services' => [
            'MvcTranslator' => $translator,
        ]]);
        $services = new ServiceManager();
        $config->configureServiceManager($services);
        $helpers = new HelperPluginManager($services);
        $helper  = $helpers->get('HeadTitle');
        $this->assertSame($translator, $helper->getTranslator());
    }

    // @codingStandardsIgnoreStart
    public function testIfHelperIsTranslatorAwareAndMvcTranslatorIsUnavailableAndTranslatorIsAvailableItWillInjectTheTranslator()
    {
        // @codingStandardsIgnoreEnd
        $translator = new Translator();
        $config = new Config(['services' => [
            'Translator' => $translator,
        ]]);
        $services = new ServiceManager();
        $config->configureServiceManager($services);
        $helpers = new HelperPluginManager($services);
        $helper  = $helpers->get('HeadTitle');
        $this->assertSame($translator, $helper->getTranslator());
    }

    // @codingStandardsIgnoreStart
    public function testIfHelperIsTranslatorAwareAndBothMvcTranslatorAndTranslatorAreUnavailableAndTranslatorInterfaceIsAvailableItWillInjectTheTranslator()
    {
        // @codingStandardsIgnoreEnd
        $translator = new Translator();
        $config = new Config(['services' => [
            TranslatorInterface::class => $translator,
        ]]);
        $services = new ServiceManager();
        $config->configureServiceManager($services);
        $helpers = new HelperPluginManager($services);
        $helper  = $helpers->get('HeadTitle');
        $this->assertSame($translator, $helper->getTranslator());
    }

    /**
     * @group 47
     */
    public function testInjectTranslatorWillReturnEarlyIfThePluginManagerDoesNotHaveAParentContainer()
    {
        if (method_exists($this->helpers, 'configure')) {
            $this->markTestSkipped(
                'Skip test when testing against laminas-servicemanager v3, as that implementation '
                . 'guarantees a parent container in plugin managers'
            );
        }
        $helpers = new HelperPluginManager();
        $helper = new HeadTitle();
        $this->assertNull($helpers->injectTranslator($helper, $helpers));
        $this->assertNull($helper->getTranslator());
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

    public function testCanUseCallableAsHelper()
    {
        $helper = function () {
        };
        $helpers = new HelperPluginManager(new ServiceManager());
        $config = new Config(
            [
                'factories' => [
                    'foo' => function ($container) use ($helper) {
                        return $helper;
                    },
                ]
            ]
        );
        $config->configureServiceManager($helpers);
        $this->assertSame($helper, $helpers->get('foo'));
    }

    private function getServiceNotFoundException(HelperPluginManager $manager)
    {
        if (method_exists($manager, 'configure')) {
            return InvalidServiceException::class;
        }
        return InvalidHelperException::class;
    }
}
