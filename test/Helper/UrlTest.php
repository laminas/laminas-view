<?php

namespace LaminasTest\View\Helper;

use Laminas\Mvc\ModuleRouteListener;
use Laminas\Mvc\MvcEvent;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\Router\Http\TreeRouteStack;
use Laminas\Router\Http\Wildcard;
use Laminas\Router\RouteMatch;
use Laminas\Router\SimpleRouteStack;
use Laminas\View\Exception;
use Laminas\View\Helper\Url as UrlHelper;
use PHPUnit\Framework\TestCase;

/**
 * Laminas\View\Helper\Url Test
 *
 * Tests formText helper, including some common functionality of all form helpers
 *
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class UrlTest extends TestCase
{
    /**
     * @var SimpleRouteStack
     */
    private $router;

    /**
     * @var UrlHelper
     */
    private $url;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $router = new SimpleRouteStack();
        $router->addRoute('home', [
            'type' => Literal::class,
            'options' => [
                'route' => '/',
            ]
        ]);
        $router->addRoute('default', [
                'type' => Segment::class,
                'options' => [
                    'route' => '/:controller[/:action]',
                ]
        ]);
        $this->router = $router;

        $this->url = new UrlHelper;
        $this->url->setRouter($router);
    }

    public function testHelperHasHardDependencyWithRouter(): void
    {
        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('No RouteStackInterface instance provided');
        $url = new UrlHelper;
        $url('home');
    }

    public function testHomeRoute(): void
    {
        $url = $this->url->__invoke('home');
        $this->assertEquals('/', $url);
    }

    public function testModuleRoute(): void
    {
        $url = $this->url->__invoke('default', ['controller' => 'ctrl', 'action' => 'act']);
        $this->assertEquals('/ctrl/act', $url);
    }

    public function testModel(): void
    {
        $it = new \ArrayIterator(['controller' => 'ctrl', 'action' => 'act']);

        $url = $this->url->__invoke('default', $it);
        $this->assertEquals('/ctrl/act', $url);
    }

    public function testThrowsExceptionOnInvalidParams(): void
    {
        $this->expectException(\Laminas\View\Exception\InvalidArgumentException::class);
        $this->url->__invoke('default', 'invalid params');
    }

    public function testPluginWithoutRouteMatchesInEventRaisesExceptionWhenNoRouteProvided(): void
    {
        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('RouteMatch');
        $this->url->__invoke();
    }

    public function testPluginWithRouteMatchesReturningNoMatchedRouteNameRaisesExceptionWhenNoRouteProvided(): void
    {
        $this->url->setRouteMatch(new RouteMatch([]));
        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('matched');
        $this->url->__invoke();
    }

    public function testPassingNoArgumentsWithValidRouteMatchGeneratesUrl(): void
    {
        $routeMatch = new RouteMatch([]);
        $routeMatch->setMatchedRouteName('home');
        $this->url->setRouteMatch($routeMatch);
        $url = $this->url->__invoke();
        $this->assertEquals('/', $url);
    }

    public function testCanReuseMatchedParameters(): void
    {
        $this->router->addRoute('replace', [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/:controller/:action',
                'defaults' => [
                    'controller' => 'LaminasTest\Mvc\Controller\TestAsset\SampleController',
                ],
            ],
        ]);
        $routeMatch = new RouteMatch([
            'controller' => 'foo',
        ]);
        $routeMatch->setMatchedRouteName('replace');
        $this->url->setRouteMatch($routeMatch);
        $url = $this->url->__invoke('replace', ['action' => 'bar'], [], true);
        $this->assertEquals('/foo/bar', $url);
    }

    public function testCanPassBooleanValueForThirdArgumentToAllowReusingRouteMatches(): void
    {
        $this->router->addRoute('replace', [
            'type' => Segment::class,
            'options' => [
                'route'    => '/:controller/:action',
                'defaults' => [
                    'controller' => 'LaminasTest\Mvc\Controller\TestAsset\SampleController',
                ],
            ],
        ]);
        $routeMatch = new RouteMatch([
            'controller' => 'foo',
        ]);
        $routeMatch->setMatchedRouteName('replace');
        $this->url->setRouteMatch($routeMatch);
        $url = $this->url->__invoke('replace', ['action' => 'bar'], true);
        $this->assertEquals('/foo/bar', $url);
    }

    public function testRemovesModuleRouteListenerParamsWhenReusingMatchedParameters(): void
    {
        $router = new TreeRouteStack();
        $router->addRoute('default', [
            'type' => Segment::class,
            'options' => [
                'route'    => '/:controller/:action',
                'defaults' => [
                    ModuleRouteListener::MODULE_NAMESPACE => 'LaminasTest\Mvc\Controller\TestAsset',
                    'controller' => 'SampleController',
                    'action'     => 'Dash'
                ]
            ],
            'child_routes' => [
                'wildcard' => [
                    'type'    => Wildcard::class,
                    'options' => [
                        'param_delimiter'     => '=',
                        'key_value_delimiter' => '%'
                    ]
                ]
            ]
        ]);
        $routeMatch = new RouteMatch([
            ModuleRouteListener::MODULE_NAMESPACE => 'LaminasTest\Mvc\Controller\TestAsset',
            'controller' => 'Rainbow'
        ]);
        $routeMatch->setMatchedRouteName('default/wildcard');

        $event = new MvcEvent();
        $event->setRouter($router)
              ->setRouteMatch($routeMatch);

        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->onRoute($event);

        $helper = new UrlHelper();
        $helper->setRouter($router);
        $helper->setRouteMatch($routeMatch);

        $url = $helper->__invoke('default/wildcard', ['Twenty' => 'Cooler'], true);
        $this->assertEquals('/Rainbow/Dash=Twenty%Cooler', $url);
    }

    public function testAcceptsNextGenRouterToSetRouter(): void
    {
        $router = new SimpleRouteStack();
        $url = new UrlHelper();
        $url->setRouter($router);

        $urlReflection = new \ReflectionObject($url);
        $routerProperty = $urlReflection->getProperty('router');
        $routerProperty->setAccessible(true);
        $routerPropertyValue = $routerProperty->getValue($url);

        $this->assertSame($router, $routerPropertyValue);
    }

    public function testAcceptsNextGenRouteMatche(): void
    {
        $routeMatch = new RouteMatch([]);
        $url = new UrlHelper();
        $url->setRouteMatch($routeMatch);

        $routeMatchReflection = new \ReflectionObject($url);
        $routeMatchProperty = $routeMatchReflection->getProperty('routeMatch');
        $routeMatchProperty->setAccessible(true);
        $routeMatchPropertyValue = $routeMatchProperty->getValue($url);

        $this->assertSame($routeMatch, $routeMatchPropertyValue);
    }
}
