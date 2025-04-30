<?php

declare(strict_types=1);

namespace Laminas\View\Helper\Service;

use Laminas\Authentication\AuthenticationService;
use Laminas\Authentication\AuthenticationServiceInterface;
use Laminas\View\Helper\Identity;
use Psr\Container\ContainerInterface;

use function assert;

final class IdentityFactory
{
    public function __invoke(ContainerInterface $container): Identity
    {
        return new Identity($this->discoverAuthenticationService($container));
    }

    private function discoverAuthenticationService(ContainerInterface $container): ?AuthenticationServiceInterface
    {
        if ($container->has(AuthenticationService::class)) {
            $service1 = $container->get(AuthenticationService::class);
            assert($service1 instanceof AuthenticationServiceInterface);

            return $service1;
        }

        if ($container->has(AuthenticationServiceInterface::class)) {
            $service2 = $container->get(AuthenticationServiceInterface::class);
            assert($service2 instanceof AuthenticationServiceInterface);

            return $service2;
        }

        return null;
    }
}
