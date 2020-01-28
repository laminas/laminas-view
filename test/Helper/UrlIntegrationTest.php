<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\Helper;

use Laminas\Console\Console;
use Laminas\ServiceManager\ServiceManager;
use Laminas\ServiceManager\Config as ServiceManagerConfig;
use Laminas\View\Helper\Url as UrlHelper;

/**
 * url() helper test -- tests integration with MVC
 *
 * @category   Laminas
 * @package    Laminas_View
 * @subpackage UnitTests
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class UrlIntegrationTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $config = array(
            'router' => array(
                'routes' => array(
                    'test' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/test',
                            'defaults' => array(
                                'controller' => 'Test\Controller\Test',
                            ),
                        ),
                    ),
                ),
            ),
            'console' => array(
                'router' => array(
                    'routes' => array(
                        'test' => array(
                            'type' => 'Simple',
                            'options' => array(
                                'route' => 'test this',
                                'defaults' => array(
                                    'controller' => 'Test\Controller\TestConsole',
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );
        $serviceConfig = array(
            'invokables' => array(
                'SharedEventManager' => 'Laminas\EventManager\SharedEventManager',
                'DispatchListener'   => 'Laminas\Mvc\DispatchListener',
                'RouteListener'      => 'Laminas\Mvc\RouteListener',
                'SendResponseListener' => 'Laminas\Mvc\SendResponseListener'
            ),
            'factories' => array(
                'Application'             => 'Laminas\Mvc\Service\ApplicationFactory',
                'ConsoleViewManager'      => 'Laminas\Mvc\Service\ConsoleViewManagerFactory',
                'EventManager'            => 'Laminas\Mvc\Service\EventManagerFactory',
                'ViewHelperManager'       => 'Laminas\Mvc\Service\ViewHelperManagerFactory',
                'Request'                 => 'Laminas\Mvc\Service\RequestFactory',
                'Response'                => 'Laminas\Mvc\Service\ResponseFactory',
                'Router'                  => 'Laminas\Mvc\Service\RouterFactory',
                'ConsoleRouter'           => 'Laminas\Mvc\Service\RouterFactory',
                'HttpRouter'              => 'Laminas\Mvc\Service\RouterFactory',
                'HttpViewManager'         => 'Laminas\Mvc\Service\HttpViewManagerFactory',
                'RoutePluginManager'      => 'Laminas\Mvc\Service\RoutePluginManagerFactory',
                'ViewManager'             => 'Laminas\Mvc\Service\ViewManagerFactory',
                'ViewResolver'            => 'Laminas\Mvc\Service\ViewResolverFactory',
                'ViewTemplateMapResolver' => 'Laminas\Mvc\Service\ViewTemplateMapResolverFactory',
                'ViewTemplatePathStack'   => 'Laminas\Mvc\Service\ViewTemplatePathStackFactory',
            ),
            'shared' => array(
                'EventManager' => false,
            ),
        );
        $serviceConfig = new ServiceManagerConfig($serviceConfig);

        $this->serviceManager = new ServiceManager($serviceConfig);
        $this->serviceManager->setService('Config', $config);
        $this->serviceManager->setAlias('Configuration', 'Config');
    }

    public function testUrlHelperWorksUnderNormalHttpParadigms()
    {
        Console::overrideIsConsole(false);
        $this->serviceManager->get('Application')->bootstrap();
        $request = $this->serviceManager->get('Request');
        $this->assertInstanceOf('Laminas\Http\Request', $request);
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
        $this->assertInstanceOf('Laminas\Http\Request', $request);
        $router = $this->serviceManager->get('Router');
        $router->setRequestUri($request->getUri());
        $request->setUri('http://example.com/test');
        $viewHelpers = $this->serviceManager->get('ViewHelperManager');
        $urlHelper   = $viewHelpers->get('url');
        $test        = $urlHelper('test', array(), array('force_canonical' => true));
        $this->assertContains('/test', $test);
    }

    public function testUrlHelperUnderConsoleParadigmShouldReturnHttpRoutes()
    {
        Console::overrideIsConsole(true);
        $this->serviceManager->get('Application')->bootstrap();
        $request = $this->serviceManager->get('Request');
        $this->assertInstanceOf('Laminas\Console\Request', $request);
        $viewHelpers = $this->serviceManager->get('ViewHelperManager');
        $urlHelper   = $viewHelpers->get('url');
        $test        = $urlHelper('test');
        $this->assertEquals('/test', $test);
    }
}
