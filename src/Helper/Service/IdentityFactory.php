<?php

declare(strict_types=1);

namespace Laminas\View\Helper\Service;

use Interop\Container\ContainerInterface;
use Laminas\Authentication\AuthenticationService;
use Laminas\Authentication\AuthenticationServiceInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Helper\Identity;

class IdentityFactory implements FactoryInterface
{
    /**
     * @param string $name
     * @param null|array $options
     * @return Identity
     */
    public function __invoke(ContainerInterface $container, $name, ?array $options = null)
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

    /**
     * @return null|AuthenticationServiceInterface
     */
    private function discoverAuthenticationService(ContainerInterface $container)
    {
        if ($container->has(AuthenticationService::class)) {
            return $container->get(AuthenticationService::class);
        }

        if ($container->has(\Zend\Authentication\AuthenticationService::class)) {
            return $container->get(\Zend\Authentication\AuthenticationService::class);
        }

        return $container->has(AuthenticationServiceInterface::class)
            ? $container->get(AuthenticationServiceInterface::class)
            : ($container->has(\Zend\Authentication\AuthenticationServiceInterface::class)
                ? $container->get(\Zend\Authentication\AuthenticationServiceInterface::class)
                : null);
    }
}
