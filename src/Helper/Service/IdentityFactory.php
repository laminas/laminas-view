<?php

declare(strict_types=1);

namespace Laminas\View\Helper\Service;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Helper\Identity;

use function is_object;

/**
 * @psalm-suppress DeprecatedInterface
 */
class IdentityFactory implements FactoryInterface
{
    /**
     * @param string|null $name
     * @param array<array-key, mixed>|null $options
     * @return Identity
     */
    public function __invoke(ContainerInterface $container, $name, ?array $options = null)
    {
        return new Identity($this->discoverAuthenticationService($container));
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

    private function discoverAuthenticationService(ContainerInterface $container): ?object
    {
        // phpcs:disable WebimpressCodingStandard.Formatting.StringClassReference
        $search = [
            'Laminas\Authentication\AuthenticationService',
            'Laminas\Authentication\AuthenticationServiceInterface',
            'Zend\Authentication\AuthenticationService',
            'Zend\Authentication\AuthenticationServiceInterface',
        ];
        // phpcs:enable

        foreach ($search as $id) {
            if (! $container->has($id)) {
                continue;
            }

            $service = $container->get($id);
            if (! is_object($service)) {
                continue;
            }

            return $service;
        }

        return null;
    }
}
