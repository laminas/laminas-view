<?php

namespace LaminasTest\View\Helper\Service;

use Laminas\Authentication\AuthenticationService;
use Laminas\Authentication\AuthenticationServiceInterface;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Helper\Identity;
use Laminas\View\Helper\Service\IdentityFactory;
use Laminas\View\HelperPluginManager;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class IdentityFactoryTest extends TestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        $this->services = $this->prophesize(ServiceManager::class);
        $this->helpers  = new HelperPluginManager($this->services->reveal());
    }

    public function getContainerForFactory()
    {
        if (method_exists($this->helpers, 'configure')) {
            return $this->services->reveal();
        }
        return $this->helpers;
    }

    public function testFactoryReturnsEmptyIdentityIfNoAuthenticationServicePresent(): void
    {
        $this->services->has(AuthenticationService::class)->willReturn(false);

        $this->services->has(\Zend\Authentication\AuthenticationService::class)->willReturn(false);
        $this->services->get(AuthenticationService::class)->shouldNotBeCalled();
        $this->services->get(\Zend\Authentication\AuthenticationService::class)->shouldNotBeCalled();
        $this->services->has(AuthenticationServiceInterface::class)->willReturn(false);
        $this->services->has(\Zend\Authentication\AuthenticationServiceInterface::class)->willReturn(false);
        $this->services->get(AuthenticationServiceInterface::class)->shouldNotBeCalled();
        $this->services->get(\Zend\Authentication\AuthenticationServiceInterface::class)->shouldNotBeCalled();

        $factory = new IdentityFactory();

        $plugin = $factory($this->getContainerForFactory(), Identity::class);

        $this->assertInstanceOf(Identity::class, $plugin);
        $this->assertNull($plugin->getAuthenticationService());
    }

    public function testFactoryReturnsIdentityWithConfiguredAuthenticationServiceWhenPresent(): void
    {
        $authentication = $this->prophesize(AuthenticationService::class);

        $this->services->has(AuthenticationService::class)->willReturn(true);
        $this->services->get(AuthenticationService::class)->will([$authentication, 'reveal']);
        $this->services->has(AuthenticationServiceInterface::class)->willReturn(false);
        $this->services->has(\Zend\Authentication\AuthenticationServiceInterface::class)->willReturn(false);
        $this->services->get(AuthenticationServiceInterface::class)->shouldNotBeCalled();
        $this->services->get(\Zend\Authentication\AuthenticationServiceInterface::class)->shouldNotBeCalled();

        $factory = new IdentityFactory();

        $plugin = $factory($this->getContainerForFactory(), Identity::class);

        $this->assertInstanceOf(Identity::class, $plugin);
        $this->assertSame($authentication->reveal(), $plugin->getAuthenticationService());
    }

    public function testFactoryReturnsIdentityWithConfiguredAuthenticationServiceInterfaceWhenPresent(): void
    {
        $authentication = $this->prophesize(AuthenticationServiceInterface::class);

        $this->services->has(AuthenticationService::class)->willReturn(false);

        $this->services->has(\Zend\Authentication\AuthenticationService::class)->willReturn(false);
        $this->services->get(AuthenticationService::class)->shouldNotBeCalled();
        $this->services->get(\Zend\Authentication\AuthenticationService::class)->shouldNotBeCalled();
        $this->services->has(AuthenticationServiceInterface::class)->willReturn(true);
        $this->services->get(AuthenticationServiceInterface::class)->will([$authentication, 'reveal']);

        $factory = new IdentityFactory();

        $plugin = $factory($this->getContainerForFactory(), Identity::class);

        $this->assertInstanceOf(Identity::class, $plugin);
        $this->assertSame($authentication->reveal(), $plugin->getAuthenticationService());
    }
}
