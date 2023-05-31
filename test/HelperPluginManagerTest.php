<?php

declare(strict_types=1);

namespace LaminasTest\View;

use Laminas\I18n\Translator\Translator;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\Mvc\I18n\Translator as MvcTranslator;
use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Helper\Doctype;
use Laminas\View\Helper\HeadTitle;
use Laminas\View\Helper\HelperInterface;
use Laminas\View\Helper\Identity;
use Laminas\View\Helper\Url;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;
use PHPUnit\Framework\TestCase;

class HelperPluginManagerTest extends TestCase
{
    private HelperPluginManager $helpers;

    protected function setUp(): void
    {
        $this->helpers = new HelperPluginManager(new ServiceManager());
    }

    public function testViewIsNullByDefault(): void
    {
        $this->assertNull($this->helpers->getRenderer());
    }

    public function testAllowsInjectingRenderer(): void
    {
        $renderer = new PhpRenderer();
        $this->helpers->setRenderer($renderer);
        $this->assertSame($renderer, $this->helpers->getRenderer());
    }

    public function testInjectsRendererToHelperWhenRendererIsPresent(): void
    {
        $renderer = new PhpRenderer();
        $this->helpers->setRenderer($renderer);
        $helper = $this->helpers->get(Doctype::class);
        $this->assertSame($renderer, $helper->getView());
    }

    public function testNoRendererInjectedInHelperWhenRendererIsNotPresent(): void
    {
        $helper = $this->helpers->get(Doctype::class);
        $this->assertNull($helper->getView());
    }

    public function testRegisteringInvalidHelperRaisesInvalidServiceException(): void
    {
        $helpers = new HelperPluginManager(new ServiceManager(), [
            'factories' => [
                'test' => fn() => $this,
            ],
        ]);
        $this->expectException(InvalidServiceException::class);
        $helpers->get('test');
    }

    public function testRequestingAnUnregisteredHelperRaisesServiceNotFoundException(): void
    {
        $helpers = new HelperPluginManager(new ServiceManager(), []);
        $this->expectException(ServiceNotFoundException::class);
        $helpers->get('test');
    }

    public function testDefinesFactoryForIdentityPlugin(): void
    {
        $this->assertTrue($this->helpers->has('identity'));
        $this->assertTrue($this->helpers->has(Identity::class));
    }

    public function testIfHelperIsTranslatorAwareAndMvcTranslatorIsAvailableItWillInjectTheMvcTranslator(): void
    {
        $translator = new MvcTranslator(
            $this->getMockBuilder(TranslatorInterface::class)->getMock()
        );
        $config     = new Config([
            'services' => [
                'MvcTranslator' => $translator,
            ],
        ]);
        $services   = new ServiceManager();
        $config->configureServiceManager($services);
        $helpers = new HelperPluginManager($services);
        $helper  = $helpers->get(HeadTitle::class);
        $this->assertSame($translator, $helper->getTranslator());
    }

    // @codingStandardsIgnoreStart
    public function testIfHelperIsTranslatorAwareAndMvcTranslatorIsUnavailableAndTranslatorIsAvailableItWillInjectTheTranslator(): void
    {
        // @codingStandardsIgnoreEnd
        $translator = new Translator();
        $config     = new Config([
            'services' => [
                'Translator' => $translator,
            ],
        ]);
        $services   = new ServiceManager();
        $config->configureServiceManager($services);
        $helpers = new HelperPluginManager($services);
        $helper  = $helpers->get(HeadTitle::class);
        $this->assertSame($translator, $helper->getTranslator());
    }

    // @codingStandardsIgnoreStart
    public function testIfHelperIsTranslatorAwareAndBothMvcTranslatorAndTranslatorAreUnavailableAndTranslatorInterfaceIsAvailableItWillInjectTheTranslator(): void
    {
        // @codingStandardsIgnoreEnd
        $translator = new Translator();
        $config     = new Config([
            'services' => [
                TranslatorInterface::class => $translator,
            ],
        ]);
        $services   = new ServiceManager();
        $config->configureServiceManager($services);
        $helpers = new HelperPluginManager($services);
        $helper  = $helpers->get(HeadTitle::class);
        $this->assertSame($translator, $helper->getTranslator());
    }

    public function testInjectTranslatorWillReturnEarlyIfTheHelperHasTranslatorAlready(): void
    {
        $translatorA = new Translator();
        $translatorB = new Translator();
        $config      = new Config([
            'services' => [
                'Translator' => $translatorB,
            ],
        ]);
        $services    = new ServiceManager();
        $config->configureServiceManager($services);
        $helpers = new HelperPluginManager($services);
        $helpers->setFactory(
            'TestHelper',
            function () use ($translatorA) {
                $helper = new HeadTitle();
                $helper->setTranslator($translatorA);
                return $helper;
            }
        );
        $helperB = $helpers->get('TestHelper');
        self::assertInstanceOf(HeadTitle::class, $helperB);
        $this->assertSame($translatorA, $helperB->getTranslator());
    }

    public function testCanOverrideAFactoryViaConfigurationPassedToConstructor(): void
    {
        $helper  = $this->createMock(HelperInterface::class);
        $helpers = new HelperPluginManager(new ServiceManager());
        $config  = new Config(
            [
                'factories' => [
                    Url::class => static fn($container) => $helper,
                ],
            ]
        );
        $config->configureServiceManager($helpers);
        $this->assertSame($helper, $helpers->get(Url::class));
    }

    public function testCanUseCallableAsHelper(): void
    {
        $helper  = function (): void {
        };
        $helpers = new HelperPluginManager(new ServiceManager());
        $config  = new Config(
            [
                'factories' => [
                    'foo' => static fn($container) => $helper,
                ],
            ]
        );
        $config->configureServiceManager($helpers);
        $this->assertSame($helper, $helpers->get('foo'));
    }

    public function testDoctypeFactoryExists(): void
    {
        self::assertTrue($this->helpers->has(Doctype::class));
    }
}
