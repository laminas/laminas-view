<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\Service;

use Laminas\Authentication\AuthenticationService;
use Laminas\Authentication\AuthenticationServiceInterface;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Helper\Identity;
use Laminas\View\Helper\Service\IdentityFactory;
use Laminas\View\HelperPluginManager;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;

use function method_exists;

class IdentityFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy&ServiceManager&ContainerInterface */
    private $services;
    /** @var HelperPluginManager&ContainerInterface */
    private $helpers;

    protected function setUp(): void
    {
        $this->services = $this->prophesize(ServiceManager::class);
        $this->helpers  = new HelperPluginManager($this->services->reveal());
    }

    public function getContainerForFactory(): ContainerInterface
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

    public function testThatAHelperCanBeCreatedWhenThereAreNoAuthenticationServicesFound(): void
    {
        $container = $this->createMock(ServiceManager::class);
        $container->expects(self::exactly(4))
            ->method('has')
            ->willReturn(false);

        $container->expects(self::never())->method('get');

        $factory = new IdentityFactory();
        $helper  = $factory($container, null);
        self::assertNull($helper());
    }

    /** @return array<array-key, array{0: string}> */
    public function serviceNameProvider(): array
    {
        // phpcs:disable WebimpressCodingStandard.Formatting.StringClassReference
        return [
            ['Laminas\Authentication\AuthenticationService'],
            ['Laminas\Authentication\AuthenticationServiceInterface'],
            ['Zend\Authentication\AuthenticationService'],
            ['Zend\Authentication\AuthenticationServiceInterface'],
        ];
        // phpcs:enable
    }

    private function authService(?string $id): object
    {
        return new class ($id) {
            private ?string $id;

            public function __construct(?string $id)
            {
                $this->id = $id;
            }

            public function hasIdentity(): bool
            {
                return $this->id !== null;
            }

            public function getIdentity(): ?string
            {
                return $this->id;
            }
        };
    }

    /** @dataProvider serviceNameProvider */
    public function testThatAFoundAuthenticationServiceWillBeUsed(string $serviceId): void
    {
        $service = $this->authService('james bond');

        $container = $this->createMock(ServiceManager::class);
        $container->expects(self::atLeast(1))
            ->method('has')
            ->willReturnCallback(static fn (string $argument): bool => $argument === $serviceId);

        $container->expects(self::once())
            ->method('get')
            ->with($serviceId)
            ->willReturn($service);

        $factory = new IdentityFactory();
        $helper  = $factory($container, null);
        self::assertEquals('james bond', $helper());
    }
}
