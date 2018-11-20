<?php
/**
 * @see       https://github.com/zendframework/zend-view for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-view/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\View\Helper\Service;

use PHPUnit\Framework\TestCase;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Helper\Identity;
use Zend\View\Helper\Service\IdentityFactory;

class IdentityFactoryTest extends TestCase
{
    public function testFactoryReturnsEmptyIdentityIfNoAuthenticationServicePresent()
    {
        $container = $this->prophesize(ServiceManager::class);
        $container->has(AuthenticationService::class)->willReturn(false);
        $container->get(AuthenticationService::class)->shouldNotBeCalled();
        $container->has(AuthenticationServiceInterface::class)->willReturn(false);
        $container->get(AuthenticationServiceInterface::class)->shouldNotBeCalled();

        $factory = new IdentityFactory();
        $plugin = $factory($container->reveal(), Identity::class);

        $this->assertInstanceOf(Identity::class, $plugin);
        $this->assertNull($plugin->getAuthenticationService());
    }

    public function testFactoryReturnsIdentityWithConfiguredAuthenticationServiceWhenPresent()
    {
        $container = $this->prophesize(ServiceManager::class);
        $authentication = $this->prophesize(AuthenticationService::class);

        $container->has(AuthenticationService::class)->willReturn(true);
        $container->get(AuthenticationService::class)->will([$authentication, 'reveal']);
        $container->has(AuthenticationServiceInterface::class)->willReturn(false);
        $container->get(AuthenticationServiceInterface::class)->shouldNotBeCalled();

        $factory = new IdentityFactory();
        $plugin = $factory($container->reveal(), Identity::class);

        $this->assertInstanceOf(Identity::class, $plugin);
        $this->assertSame($authentication->reveal(), $plugin->getAuthenticationService());
    }

    public function testFactoryReturnsIdentityWithConfiguredAuthenticationServiceInterfaceWhenPresent()
    {
        $container = $this->prophesize(ServiceManager::class);
        $authentication = $this->prophesize(AuthenticationServiceInterface::class);

        $container->has(AuthenticationService::class)->willReturn(false);
        $container->get(AuthenticationService::class)->shouldNotBeCalled();
        $container->has(AuthenticationServiceInterface::class)->willReturn(true);
        $container->get(AuthenticationServiceInterface::class)->will([$authentication, 'reveal']);

        $factory = new IdentityFactory();
        $plugin = $factory($container->reveal(), Identity::class);

        $this->assertInstanceOf(Identity::class, $plugin);
        $this->assertSame($authentication->reveal(), $plugin->getAuthenticationService());
    }
}
