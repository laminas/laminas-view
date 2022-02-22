<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\Service;

use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Helper\Service\IdentityFactory;
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
