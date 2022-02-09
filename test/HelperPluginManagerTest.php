<?php

declare(strict_types=1);

namespace LaminasTest\View;

use Generator;
use Laminas\Authentication\AuthenticationService;
use Laminas\I18n\Translator\Translator;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\Mvc\I18n\Translator as MvcTranslator;
use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\PluginManagerInterface;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\ConfigProvider;
use Laminas\View\Exception\InvalidHelperException;
use Laminas\View\Helper\Doctype;
use Laminas\View\Helper\HeadTitle;
use Laminas\View\Helper\HelperInterface;
use Laminas\View\Helper\Url;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;
use LaminasTest\View\TestAsset\Invokable;
use LaminasTest\View\TestAsset\UnsupportedDescendantOfPluginManagerWithConstructor;
use LaminasTest\View\TestAsset\UnsupportedDescendantOfPluginManagerWithPropertyValues;
use PHPUnit\Framework\TestCase;

use function assert;
use function count;
use function in_array;
use function is_callable;
use function method_exists;

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
        $helper = $this->helpers->get('doctype');
        $this->assertSame($renderer, $helper->getView());
    }

    public function testNoRendererInjectedInHelperWhenRendererIsNotPresent(): void
    {
        $helper = $this->helpers->get('doctype');
        $this->assertNull($helper->getView());
    }

    public function testRegisteringInvalidHelperRaisesException(): void
    {
        $helpers = new HelperPluginManager(new ServiceManager(), [
            'factories' => [
                'test' => fn() => $this,
            ],
        ]);
        $this->expectException($this->getServiceNotFoundException($helpers));
        $helpers->get('test');
    }

    public function testLoadingInvalidHelperRaisesException(): void
    {
        $helpers = new HelperPluginManager(new ServiceManager(), [
            'invokables' => [
                'test' => static::class,
            ],
        ]);
        $this->expectException($this->getServiceNotFoundException($helpers));
        $helpers->get('test');
    }

    public function testDefinesFactoryForIdentityPlugin(): void
    {
        $this->assertTrue($this->helpers->has('identity'));
    }

    public function testIdentityFactoryCanInjectAuthenticationServiceIfInParentServiceManager(): void
    {
        $config   = new Config([
            'invokables' => [
                AuthenticationService::class => AuthenticationService::class,
            ],
        ]);
        $services = new ServiceManager();
        $config->configureServiceManager($services);
        $helpers  = new HelperPluginManager($services);
        $identity = $helpers->get('identity');
        $expected = $services->get(AuthenticationService::class);
        $this->assertSame($expected, $identity->getAuthenticationService());
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
        $helper  = $helpers->get('HeadTitle');
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
        $helper  = $helpers->get('HeadTitle');
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
        $helper  = $helpers->get('HeadTitle');
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
        $this->assertSame($translatorA, $helperB->getTranslator());
    }

    public function testCanOverrideAFactoryViaConfigurationPassedToConstructor(): void
    {
        $helper  = $this->createMock(HelperInterface::class);
        $helpers = new HelperPluginManager(new ServiceManager());
        $config  = new Config(
            [
                'factories' => [
                    Url::class => static fn() => $helper,
                ],
            ]
        );
        $config->configureServiceManager($helpers);
        $this->assertSame($helper, $helpers->get('url'));
    }

    public function testCanUseCallableAsHelper(): void
    {
        $helper  = static fn (): string => 'Foo';
        $helpers = new HelperPluginManager(new ServiceManager());
        $config  = new Config(
            [
                'factories' => [
                    'foo' => static fn() => $helper,
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

    /**
     * @psalm-return InvalidHelperException::class|InvalidServiceException::class
     */
    private function getServiceNotFoundException(HelperPluginManager $manager): string
    {
        if (method_exists($manager, 'configure')) {
            return InvalidServiceException::class;
        }
        return InvalidHelperException::class;
    }

    /**
     * @psalm-suppress DeprecatedClass
     */
    public function testThatHelpersConfiguredInADescendantConstructorAddToTheDefaultValues(): void
    {
        $container = new ServiceManager();
        $manager   = new UnsupportedDescendantOfPluginManagerWithConstructor($container);
        self::assertTrue($manager->has('doctype'));
        self::assertTrue($manager->has('Laminas\Test\FactoryBackedHelper'));
        self::assertTrue($manager->has('aliasForTestHelper'));
        $helper = $manager->get('aliasForTestHelper');
        assert(is_callable($helper));
        self::assertEquals(UnsupportedDescendantOfPluginManagerWithConstructor::EXPECTED_HELPER_OUTPUT, $helper());
    }

    /**
     * @psalm-suppress DeprecatedClass
     */
    public function testThatDefaultPropertiesInADescendantOverwriteDefaultHelperConfiguration(): void
    {
        $container = new ServiceManager();
        $manager   = new UnsupportedDescendantOfPluginManagerWithPropertyValues($container);
        self::assertFalse($manager->has('doctype'));
        self::assertTrue($manager->has(Invokable::class));
        self::assertTrue($manager->has('aliasForTestHelper'));
        $helper = $manager->get('aliasForTestHelper');
        self::assertInstanceOf(Invokable::class, $helper);
    }

    /** @return Generator<string, array{0: string, 1:string}> */
    public function standardAliasProvider(): Generator
    {
        // @codingStandardsIgnoreStart
        $helpersToIgnore = [
            'Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger',
            'Laminas\View\Helper\FlashMessenger',
            'Zend\View\Helper\FlashMessenger',
            'zendviewhelperflashmessenger',
            'flashMessenger',
            'flashmessenger',
            'FlashMessenger',
            'laminasviewhelperflashmessenger',
        ];
        // @codingStandardsIgnoreEnd

        $config = (new ConfigProvider())();
        /** @psalm-var array{'aliases': array<string, string>} $helperConfig */
        $helperConfig = $config['view_helpers'] ?? [];
        self::assertArrayHasKey('aliases', $helperConfig);
        $standardAliases = $helperConfig['aliases'];

        self::assertGreaterThan(
            0,
            count($standardAliases),
            'There should be at least one helper configured by default'
        );

        foreach ($standardAliases as $aliasName => $target) {
            if (in_array($aliasName, $helpersToIgnore, true)) {
                continue;
            }

            yield $aliasName => [$aliasName, $target];
        }
    }

    /** @dataProvider standardAliasProvider */
    public function testThatAllDefaultHelpersCanBeRetrievedByAliasAndTarget(string $alias, string $target): void
    {
        $plugins = new HelperPluginManager(new ServiceManager([
            'services' => [
                'config'                  => [],
                'ControllerPluginManager' => $this->createMock(PluginManagerInterface::class),
            ],
        ]));
        self::assertTrue($plugins->has($alias));
        self::assertTrue($plugins->has($target));
        self::assertSame($plugins->get($alias), $plugins->get($target));
    }
}
