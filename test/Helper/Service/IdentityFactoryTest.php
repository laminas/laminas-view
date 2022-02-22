<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\Service;

use Laminas\Authentication\AuthenticationService;
use Laminas\Authentication\AuthenticationServiceInterface;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Helper\Identity;
use Laminas\View\Helper\Service\IdentityFactory;
use LaminasTest\View\Helper\TestAsset\AuthenticationServiceStub;
use PHPUnit\Framework\TestCase;

class IdentityFactoryTest extends TestCase
{
    public function testThatAHelperCanBeCreatedWhenThereAreNoAuthenticationServicesFound(): void
    {
        $container = $this->createMock(ServiceManager::class);
        $container->expects(self::exactly(4))
            ->method('has')
            ->willReturn(false);

        $container->expects(self::never())->method('get');

        $factory = new IdentityFactory();
        $helper  = $factory($container, null);
        self::assertInstanceOf(Identity::class, $helper);
    }

    /** @return array<array-key, array{0: string|class-string}> */
    public function serviceNameProvider(): array
    {
        // phpcs:disable WebimpressCodingStandard.Formatting.StringClassReference
        return [
            [AuthenticationService::class],
            [AuthenticationServiceInterface::class],
            ['Zend\Authentication\AuthenticationService'],
            ['Zend\Authentication\AuthenticationServiceInterface'],
        ];
        // phpcs:enable
    }

    private function authService(?string $id): AuthenticationServiceStub
    {
        return new AuthenticationServiceStub($id);
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
