<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\Navigation;

use Interop\Container\ContainerInterface;
use Laminas\I18n\Translator\Translator;
use Laminas\Navigation\Navigation as Container;
use Laminas\Navigation\Page;
use Laminas\Permissions\Acl;
use Laminas\Permissions\Acl\Role;
use Laminas\ServiceManager\PsrContainerDecorator;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View;
use Laminas\View\Helper\Navigation;
use Laminas\View\Renderer\PhpRenderer;
use ReflectionObject;
use stdClass;

use function extension_loaded;
use function restore_error_handler;
use function set_error_handler;
use function spl_object_hash;
use function str_replace;

use const PHP_EOL;

/**
 * Tests Laminas\View\Helper\Navigation
 *
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 * @psalm-suppress MissingConstructor
 */
class NavigationTest extends AbstractTest
{
    /**
     * View helper
     *
     * @var Navigation
     */
    protected $_helper; // phpcs:ignore
    private ?string $errorHandlerMessage = null;

    protected function setUp(): void
    {
        $this->_helper = new Navigation();
        parent::setUp();
    }

    public function testHelperEntryPointWithoutAnyParams(): void
    {
        $returned = $this->_helper->__invoke();
        $this->assertEquals($this->_helper, $returned);
        $this->assertEquals($this->nav1, $returned->getContainer());
    }

    public function testHelperEntryPointWithContainerParam(): void
    {
        $returned = $this->_helper->__invoke($this->nav2);
        $this->assertEquals($this->_helper, $returned);
        $this->assertEquals($this->nav2, $returned->getContainer());
    }

    public function testAcceptAclShouldReturnGracefullyWithUnknownResource(): void
    {
        // setup
        $acl = $this->getAcl();
        $this->_helper->setAcl($acl['acl']);
        $this->_helper->setRole($acl['role']);

        $accepted = $this->_helper->accept(
            new Page\Uri([
                'resource'  => 'unknownresource',
                'privilege' => 'someprivilege',
            ], false)
        );

        $this->assertEquals($accepted, false);
    }

    public function testShouldProxyToMenuHelperByDefault(): void
    {
        $this->_helper->setContainer($this->nav1);
        $this->_helper->setServiceLocator(new ServiceManager());

        // result
        $expected = $this->getExpectedFileContents('menu/default1.html');
        $actual   = $this->_helper->render();

        $this->assertEquals($expected, $actual);
    }

    public function testHasContainer(): void
    {
        $oldContainer = $this->_helper->getContainer();
        $this->_helper->setContainer(null);
        $this->assertFalse($this->_helper->hasContainer());
        $this->_helper->setContainer($oldContainer);
    }

    public function testInjectingContainer(): void
    {
        // setup
        $this->_helper->setContainer($this->nav2);
        $this->_helper->setServiceLocator(new ServiceManager());
        $expected = [
            'menu'        => $this->getExpectedFileContents('menu/default2.html'),
            'breadcrumbs' => $this->getExpectedFileContents('bc/default.html'),
        ];
        $actual   = [];

        // result
        $actual['menu'] = $this->_helper->render();
        $this->_helper->setContainer($this->nav1);
        $actual['breadcrumbs'] = $this->_helper->breadcrumbs()->render();

        $this->assertEquals($expected, $actual);
    }

    public function testDisablingContainerInjection(): void
    {
        // setup
        $this->_helper->setServiceLocator(new ServiceManager());
        $this->_helper->setInjectContainer(false);
        $this->_helper->menu()->setContainer(null);
        $this->_helper->breadcrumbs()->setContainer(null);
        $this->_helper->setContainer($this->nav2);

        // result
        $expected = [
            'menu'        => '',
            'breadcrumbs' => '',
        ];
        $actual   = [
            'menu'        => $this->_helper->render(),
            'breadcrumbs' => $this->_helper->breadcrumbs()->render(),
        ];

        $this->assertEquals($expected, $actual);
    }

    public function testMultipleNavigationsAndOneMenuDisplayedTwoTimes(): void
    {
        $this->_helper->setServiceLocator(new ServiceManager());
        $expected = $this->_helper->setContainer($this->nav1)->menu()->getContainer();
        $this->_helper->setContainer($this->nav2)->menu()->getContainer();
        $actual = $this->_helper->setContainer($this->nav1)->menu()->getContainer();

        $this->assertEquals($expected, $actual);
    }

    public function testServiceManagerIsUsedToRetrieveContainer(): void
    {
        $container      = new Container();
        $serviceManager = new ServiceManager();
        $serviceManager->setService('navigation', $container);

        new View\HelperPluginManager($serviceManager);

        $this->_helper->setServiceLocator($serviceManager);
        $this->_helper->setContainer('navigation');

        $expected = $this->_helper->getContainer();
        $actual   = $container;
        $this->assertEquals($expected, $actual);
    }

    public function testInjectingAcl(): void
    {
        // setup
        $acl = $this->getAcl();
        $this->_helper->setAcl($acl['acl']);
        $this->_helper->setRole($acl['role']);
        $this->_helper->setServiceLocator(new ServiceManager());

        $expected = $this->getExpectedFileContents('menu/acl.html');
        $actual   = $this->_helper->render();

        $this->assertEquals($expected, $actual);
    }

    public function testDisablingAclInjection(): void
    {
        // setup
        $acl = $this->getAcl();
        $this->_helper->setAcl($acl['acl']);
        $this->_helper->setRole($acl['role']);
        $this->_helper->setInjectAcl(false);
        $this->_helper->setServiceLocator(new ServiceManager());

        $expected = $this->getExpectedFileContents('menu/default1.html');
        $actual   = $this->_helper->render();

        $this->assertEquals($expected, $actual);
    }

    public function testInjectingTranslator(): void
    {
        if (! extension_loaded('intl')) {
            $this->markTestSkipped('ext/intl not enabled');
        }

        $this->_helper->setTranslator($this->getTranslator());
        $this->_helper->setServiceLocator(new ServiceManager());

        $expected = $this->getExpectedFileContents('menu/translated.html');
        $actual   = $this->_helper->render();

        $this->assertEquals($expected, $actual);
    }

    public function testDisablingTranslatorInjection(): void
    {
        $this->_helper->setTranslator($this->getTranslator());
        $this->_helper->setInjectTranslator(false);
        $this->_helper->setServiceLocator(new ServiceManager());

        $expected = $this->getExpectedFileContents('menu/default1.html');
        $actual   = $this->_helper->render();

        $this->assertEquals($expected, $actual);
    }

    public function testTranslatorMethods(): void
    {
        $translatorMock = $this->createMock(Translator::class);
        $this->_helper->setTranslator($translatorMock, 'foo');

        $this->assertEquals($translatorMock, $this->_helper->getTranslator());
        $this->assertEquals('foo', $this->_helper->getTranslatorTextDomain());
        $this->assertTrue($this->_helper->hasTranslator());
        $this->assertTrue($this->_helper->isTranslatorEnabled());

        $this->_helper->setTranslatorEnabled(false);
        $this->assertFalse($this->_helper->isTranslatorEnabled());
    }

    public function testSpecifyingDefaultProxy(): void
    {
        $expected = [
            'breadcrumbs' => $this->getExpectedFileContents('bc/default.html'),
            'menu'        => $this->getExpectedFileContents('menu/default1.html'),
        ];
        $actual   = [];

        // result
        $this->_helper->setServiceLocator(new ServiceManager());
        $this->_helper->setDefaultProxy('breadcrumbs');
        $actual['breadcrumbs'] = $this->_helper->render($this->nav1);
        $this->_helper->setDefaultProxy('menu');
        $actual['menu'] = $this->_helper->render($this->nav1);

        $this->assertEquals($expected, $actual);
    }

    public function testGetAclReturnsNullIfNoAclInstance(): void
    {
        $this->assertNull($this->_helper->getAcl());
    }

    public function testGetAclReturnsAclInstanceSetWithSetAcl(): void
    {
        $acl = new Acl\Acl();
        $this->_helper->setAcl($acl);
        $this->assertEquals($acl, $this->_helper->getAcl());
    }

    public function testGetAclReturnsAclInstanceSetWithSetDefaultAcl(): void
    {
        $acl = new Acl\Acl();
        Navigation\AbstractHelper::setDefaultAcl($acl);
        $actual = $this->_helper->getAcl();
        Navigation\AbstractHelper::setDefaultAcl(null);
        $this->assertEquals($acl, $actual);
    }

    public function testSetDefaultAclAcceptsNull(): void
    {
        $acl = new Acl\Acl();
        Navigation\AbstractHelper::setDefaultAcl($acl);
        Navigation\AbstractHelper::setDefaultAcl(null);
        $this->assertNull($this->_helper->getAcl());
    }

    public function testSetDefaultAclAcceptsNoParam(): void
    {
        $acl = new Acl\Acl();
        Navigation\AbstractHelper::setDefaultAcl($acl);
        Navigation\AbstractHelper::setDefaultAcl();
        $this->assertNull($this->_helper->getAcl());
    }

    public function testSetRoleAcceptsString(): void
    {
        $this->_helper->setRole('member');
        $this->assertEquals('member', $this->_helper->getRole());
    }

    public function testSetRoleAcceptsRoleInterface(): void
    {
        $role = new Role\GenericRole('member');
        $this->_helper->setRole($role);
        $this->assertEquals($role, $this->_helper->getRole());
    }

    public function testSetRoleAcceptsNull(): void
    {
        $this->_helper->setRole('member')->setRole(null);
        $this->assertNull($this->_helper->getRole());
    }

    public function testSetRoleAcceptsNoParam(): void
    {
        $this->_helper->setRole('member')->setRole();
        $this->assertNull($this->_helper->getRole());
    }

    public function testSetRoleThrowsExceptionWhenGivenAnInt(): void
    {
        try {
            $this->_helper->setRole(1337);
            $this->fail('An invalid argument was given, but a '
                        . 'Laminas\View\Exception\InvalidArgumentException was not thrown');
        } catch (View\Exception\ExceptionInterface $e) {
            $this->assertStringContainsString('$role must be a string', $e->getMessage());
        }
    }

    public function testSetRoleThrowsExceptionWhenGivenAnArbitraryObject(): void
    {
        try {
            $this->_helper->setRole(new stdClass());
            $this->fail('An invalid argument was given, but a '
                        . 'Laminas\View\Exception\InvalidArgumentException was not thrown');
        } catch (View\Exception\ExceptionInterface $e) {
            $this->assertStringContainsString('$role must be a string', $e->getMessage());
        }
    }

    public function testSetDefaultRoleAcceptsString(): void
    {
        $expected = 'member';
        Navigation\AbstractHelper::setDefaultRole($expected);
        $actual = $this->_helper->getRole();
        Navigation\AbstractHelper::setDefaultRole(null);
        $this->assertEquals($expected, $actual);
    }

    public function testSetDefaultRoleAcceptsRoleInterface(): void
    {
        $expected = new Role\GenericRole('member');
        Navigation\AbstractHelper::setDefaultRole($expected);
        $actual = $this->_helper->getRole();
        Navigation\AbstractHelper::setDefaultRole(null);
        $this->assertEquals($expected, $actual);
    }

    public function testSetDefaultRoleAcceptsNull(): void
    {
        Navigation\AbstractHelper::setDefaultRole(null);
        $this->assertNull($this->_helper->getRole());
    }

    public function testSetDefaultRoleAcceptsNoParam(): void
    {
        Navigation\AbstractHelper::setDefaultRole();
        $this->assertNull($this->_helper->getRole());
    }

    public function testSetDefaultRoleThrowsExceptionWhenGivenAnInt(): void
    {
        try {
            Navigation\AbstractHelper::setDefaultRole(1337);
            $this->fail('An invalid argument was given, but a '
                        . 'Laminas\View\Exception\InvalidArgumentException was not thrown');
        } catch (View\Exception\ExceptionInterface $e) {
            $this->assertStringContainsString('$role must be', $e->getMessage());
        }
    }

    public function testSetDefaultRoleThrowsExceptionWhenGivenAnArbitraryObject(): void
    {
        try {
            Navigation\AbstractHelper::setDefaultRole(new stdClass());
            $this->fail('An invalid argument was given, but a '
                        . 'Laminas\View\Exception\InvalidArgumentException was not thrown');
        } catch (View\Exception\ExceptionInterface $e) {
            $this->assertStringContainsString('$role must be', $e->getMessage());
        }
    }

    public function testMagicToStringShouldNotThrowException(): void
    {
        set_error_handler(function (int $code, string $message) {
            $this->errorHandlerMessage = $message;
        });

        $this->_helper->menu()->setPartial([1337]);
        $this->_helper->__toString();
        restore_error_handler();

        $this->assertStringContainsString('array must contain', $this->errorHandlerMessage);
    }

    public function testPageIdShouldBeNormalized(): void
    {
        $nl = PHP_EOL;

        $container = new Container([
            [
                'label' => 'Page 1',
                'id'    => 'p1',
                'uri'   => 'p1',
            ],
            [
                'label' => 'Page 2',
                'id'    => 'p2',
                'uri'   => 'p2',
            ],
        ]);

        $expected = '<ul class="navigation">' . $nl
                  . '    <li>' . $nl
                  . '        <a id="menu-p1" href="p1">Page 1</a>' . $nl
                  . '    </li>' . $nl
                  . '    <li>' . $nl
                  . '        <a id="menu-p2" href="p2">Page 2</a>' . $nl
                  . '    </li>' . $nl
                  . '</ul>';

        $this->_helper->setServiceLocator(new ServiceManager());
        $actual = $this->_helper->render($container);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @group Laminas-6854
     */
    public function testRenderInvisibleItem(): void
    {
        $container = new Container([
            [
                'label' => 'Page 1',
                'id'    => 'p1',
                'uri'   => 'p1',
            ],
            [
                'label'   => 'Page 2',
                'id'      => 'p2',
                'uri'     => 'p2',
                'visible' => false,
            ],
        ]);

        $this->_helper->setServiceLocator(new ServiceManager());
        $render = $this->_helper->menu()->render($container);

        $this->assertStringNotContainsString('p2', $render);

        $this->_helper->menu()->setRenderInvisible();

        $render = $this->_helper->menu()->render($container);

        $this->assertStringContainsString('p2', $render);
    }

    public function testMultipleNavigations(): void
    {
        $sm   = new ServiceManager();
        $nav1 = new Container();
        $nav2 = new Container();
        $sm->setService('nav1', $nav1);
        $sm->setService('nav2', $nav2);

        $helper = new Navigation();
        $helper->setServiceLocator($sm);

        $menu     = $helper('nav1')->menu();
        $actual   = spl_object_hash($nav1);
        $expected = spl_object_hash($menu->getContainer());
        $this->assertEquals($expected, $actual);

        $menu     = $helper('nav2')->menu();
        $actual   = spl_object_hash($nav2);
        $expected = spl_object_hash($menu->getContainer());
        $this->assertEquals($expected, $actual);
    }

    /**
     * @group #3859
     */
    public function testMultipleNavigationsWithDifferentHelpersAndDifferentContainers(): void
    {
        $sm   = new ServiceManager();
        $nav1 = new Container();
        $nav2 = new Container();
        $sm->setService('nav1', $nav1);
        $sm->setService('nav2', $nav2);

        $helper = new Navigation();
        $helper->setServiceLocator($sm);

        $menu     = $helper('nav1')->menu();
        $actual   = spl_object_hash($nav1);
        $expected = spl_object_hash($menu->getContainer());
        $this->assertEquals($expected, $actual);

        $breadcrumbs = $helper('nav2')->breadcrumbs();
        $actual      = spl_object_hash($nav2);
        $expected    = spl_object_hash($breadcrumbs->getContainer());
        $this->assertEquals($expected, $actual);

        $links    = $helper()->links();
        $expected = spl_object_hash($links->getContainer());
        $this->assertEquals($expected, $actual);
    }

    /**
     * @group #3859
     */
    public function testMultipleNavigationsWithDifferentHelpersAndSameContainer(): void
    {
        $sm   = new ServiceManager();
        $nav1 = new Container();
        $sm->setService('nav1', $nav1);

        $helper = new Navigation();
        $helper->setServiceLocator($sm);

        // Tests
        $menu     = $helper('nav1')->menu();
        $actual   = spl_object_hash($nav1);
        $expected = spl_object_hash($menu->getContainer());
        $this->assertEquals($expected, $actual);

        $breadcrumbs = $helper('nav1')->breadcrumbs();
        $expected    = spl_object_hash($breadcrumbs->getContainer());
        $this->assertEquals($expected, $actual);

        $links    = $helper()->links();
        $expected = spl_object_hash($links->getContainer());
        $this->assertEquals($expected, $actual);
    }

    /**
     * @group #3859
     */
    public function testMultipleNavigationsWithSameHelperAndSameContainer(): void
    {
        $sm   = new ServiceManager();
        $nav1 = new Container();
        $sm->setService('nav1', $nav1);

        $helper = new Navigation();
        $helper->setServiceLocator($sm);

        // Test
        $menu     = $helper('nav1')->menu();
        $actual   = spl_object_hash($nav1);
        $expected = spl_object_hash($menu->getContainer());
        $this->assertEquals($expected, $actual);

        $menu     = $helper('nav1')->menu();
        $expected = spl_object_hash($menu->getContainer());
        $this->assertEquals($expected, $actual);

        $menu     = $helper()->menu();
        $expected = spl_object_hash($menu->getContainer());
        $this->assertEquals($expected, $actual);
    }

    public function testSetPluginManagerAndView(): void
    {
        $pluginManager = new Navigation\PluginManager(new ServiceManager());
        $view          = new PhpRenderer();

        $helper = new Navigation();
        $helper->setPluginManager($pluginManager);
        $helper->setView($view);

        $this->assertEquals($view, $pluginManager->getRenderer());
    }

    /**
     * @group 49
     */
    public function testInjectsLazyInstantiatedPluginManagerWithCurrentServiceLocator(): void
    {
        $services = $this->createMock(ContainerInterface::class);
        $helper   = new Navigation();
        $helper->setServiceLocator($services);

        $plugins = $helper->getPluginManager();
        $this->assertInstanceOf(Navigation\PluginManager::class, $plugins);

        $pluginsReflection = new ReflectionObject($plugins);
        $creationContext   = $pluginsReflection->getProperty('creationContext');
        $creationContext->setAccessible(true);
        $creationContextValue = $creationContext->getValue($plugins);

        /** Later versions of AbstractPluginManager Decorate Psr Containers */
        if ($creationContextValue instanceof PsrContainerDecorator) {
            /** @psalm-suppress InternalMethod */
            $creationContextValue = $creationContextValue->getContainer();
        }

        $this->assertSame($creationContextValue, $services);
    }

    /** @inheritDoc */
    protected function getExpectedFileContents(string $filename): string
    {
        return str_replace("\n", PHP_EOL, parent::getExpectedFileContents($filename));
    }
}
