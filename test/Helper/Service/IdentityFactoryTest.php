<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\Service;

use Interop\Container\ContainerInterface;
use Laminas\Authentication\AuthenticationService;
use Laminas\Authentication\AuthenticationServiceInterface;
use Laminas\View\Helper\Identity;
use Laminas\View\Helper\Service\IdentityFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IdentityFactoryTest extends TestCase
{
    /** @var MockObject&ContainerInterface */
    private ContainerInterface $services;

    protected function setUp(): void
    {
        $this->services = $this->createMock(ContainerInterface::class);
    }

    public function testFactoryReturnsEmptyIdentityIfNoAuthenticationServicePresent(): void
    {
        $this->services->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive(
                [AuthenticationService::class],
                [AuthenticationServiceInterface::class]
            )->willReturn(false);

        $factory = new IdentityFactory();

        $plugin = $factory($this->services);

        $this->assertInstanceOf(Identity::class, $plugin);
        $this->assertNull($plugin->getAuthenticationService());
    }

    public function testFactoryReturnsIdentityWithConfiguredAuthenticationServiceWhenPresent(): void
    {
        $authService = $this->createMock(AuthenticationServiceInterface::class);
        $this->services->expects(self::once())
            ->method('has')
            ->with(AuthenticationService::class)
            ->willReturn(true);

        $this->services->expects(self::once())
            ->method('get')
            ->with(AuthenticationService::class)
            ->willReturn($authService);

        $factory = new IdentityFactory();

        $plugin = $factory($this->services);

        $this->assertInstanceOf(Identity::class, $plugin);
        $this->assertSame($authService, $plugin->getAuthenticationService());
    }

    public function testFactoryReturnsIdentityWithConfiguredAuthenticationServiceInterfaceWhenPresent(): void
    {
        $authService = $this->createMock(AuthenticationServiceInterface::class);

        $this->services->expects(self::exactly(2))
            ->method('has')
            ->willReturnOnConsecutiveCalls(false, true);

        $this->services->expects(self::once())
            ->method('get')
            ->with(AuthenticationServiceInterface::class)
            ->willReturn($authService);

        $factory = new IdentityFactory();

        $plugin = $factory($this->services);

        $this->assertInstanceOf(Identity::class, $plugin);
        $this->assertSame($authService, $plugin->getAuthenticationService());
    }
}
