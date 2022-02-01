<?php

declare(strict_types=1);

namespace Laminas\View\Helper\Service;

use Interop\Container\ContainerInterface;
use Laminas\Authentication\AuthenticationService;
use Laminas\Authentication\AuthenticationServiceInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Helper\Identity;

use function assert;

class IdentityFactory implements FactoryInterface
{
    /**
     * @param string|null $name
     * @param null|array $options
     * @return Identity
     */
    public function __invoke(ContainerInterface $container, $name = null, ?array $options = null)
    {
        $helper = new Identity();

        if (null !== ($authenticationService = $this->discoverAuthenticationService($container))) {
            $helper->setAuthenticationService($authenticationService);
        }

        return $helper;
    }

    /**
     * Create service
     *
     * @param string|null $rName
     * @param string|null $cName
     * @return Identity
     */
    public function createService(ServiceLocatorInterface $serviceLocator, $rName = null, $cName = null)
    {
        return $this($serviceLocator, $cName);
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
