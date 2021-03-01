<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\Helper;

use Laminas\Console\Console;
use Laminas\Http\Request as HttpRequest;
use Laminas\Mvc\Service\ServiceListenerFactory;
use Laminas\Mvc\Service\ServiceManagerConfig;
use Laminas\Router\ConfigProvider as RouterConfigProvider;
use Laminas\Router\Http;
use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\RequestInterface;
use PHPUnit\Framework\TestCase;

/**
 * url() helper test -- tests integration with MVC
 *
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class UrlIntegrationTest extends TestCase
{
    private $serviceManager;

    protected function setUp(): void
    {
        $config = [
            'router' => [
                'routes' => [
                    'test' => [
                        'type' => Http\Literal::class,
                        'options' => [
                            'route' => '/test',
                            'defaults' => [
                                'controller' => 'Test\Controller\Test',
                            ],
                        ],
                    ],
                ],
            ],
            'console' => [
                'router' => [
                    'routes' => [
                        'test' => [
                            'type' => 'Simple',
                            'options' => [
                                'route' => 'test this',
                                'defaults' => [
                                    'controller' => 'Test\Controller\TestConsole',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $serviceListenerFactory = new ServiceListenerFactory();
        $serviceListenerFactoryReflection = new \ReflectionObject($serviceListenerFactory);
        $serviceConfigReflection = $serviceListenerFactoryReflection->getProperty('defaultServiceConfig');
        $serviceConfigReflection->setAccessible(true);
        $serviceConfig = $serviceConfigReflection->getValue($serviceListenerFactory);

        $this->serviceManager = new ServiceManager();
        (new ServiceManagerConfig($serviceConfig))->configureServiceManager($this->serviceManager);

        $routerConfig = new Config((new RouterConfigProvider())->getDependencyConfig());
        $routerConfig->configureServiceManager($this->serviceManager);

        $this->serviceManager->setAllowOverride(true);
        $this->serviceManager->setService('config', $config);
        $this->serviceManager->setAlias('Configure', 'config');
        $this->serviceManager->setAllowOverride(false);
    }

    public function testUrlHelperWorksUnderNormalHttpParadigms()
    {
        Console::overrideIsConsole(false);
        $this->serviceManager->get('Application')->bootstrap();
        $request = $this->serviceManager->get('Request');
        $this->assertInstanceOf(HttpRequest::class, $request);
        $viewHelpers = $this->serviceManager->get('ViewHelperManager');
        $urlHelper   = $viewHelpers->get('url');
        $test        = $urlHelper('test');
        $this->assertEquals('/test', $test);
    }

    public function testUrlHelperWorksWithForceCanonicalFlag()
    {
        Console::overrideIsConsole(false);
        $this->serviceManager->get('Application')->bootstrap();
        $request = $this->serviceManager->get('Request');
        $this->assertInstanceOf(HttpRequest::class, $request);
        $router = $this->serviceManager->get('Router');
        $router->setRequestUri($request->getUri());
        $request->setUri('http://example.com/test');
        $viewHelpers = $this->serviceManager->get('ViewHelperManager');
        $urlHelper   = $viewHelpers->get('url');
        $test        = $urlHelper('test', [], ['force_canonical' => true]);
        $this->assertStringContainsString('/test', $test);
    }

    public function testUrlHelperUnderConsoleParadigmShouldReturnHttpRoutes()
    {
        Console::overrideIsConsole(true);
        $this->serviceManager->get('Application')->bootstrap();
        $request = $this->serviceManager->get('Request');
        /**
         * Who care what type of request it is? The important thing is that the given path is correct.
         */
        $this->assertInstanceOf(RequestInterface::class, $request);
        $viewHelpers = $this->serviceManager->get('ViewHelperManager');
        $urlHelper   = $viewHelpers->get('url');
        $test        = $urlHelper('test');
        $this->assertEquals('/test', $test);
    }
}
